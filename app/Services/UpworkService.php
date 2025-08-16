<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Token\AccessToken;

class UpworkService
{
    private UpworkProvider $provider;
    private ?AccessToken $accessToken = null;

    public function __construct(UpworkProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get authorization URL for OAuth2
     */
    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl();
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): AccessToken
    {
        $this->accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        // Cache the token
        $this->cacheToken($this->accessToken);

        return $this->accessToken;
    }

    /**
     * Get valid token (refresh if necessary)
     */
    private function getValidToken(): AccessToken
    {
        if ($this->accessToken && !$this->accessToken->hasExpired()) {
            return $this->accessToken;
        }

        $this->accessToken = $this->getCachedToken();

        if (!$this->accessToken || $this->accessToken->hasExpired()) {
            if ($this->accessToken && $this->accessToken->getRefreshToken()) {
                $this->accessToken = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $this->accessToken->getRefreshToken()
                ]);
                $this->cacheToken($this->accessToken);
            } else {
                throw new \RuntimeException('No valid access token available');
            }
        }

        return $this->accessToken;
    }

    /**
     * Cache the access token
     */
    private function cacheToken(AccessToken $token): void
    {
        Cache::put(
            'upwork_token',
            [
                'access_token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires' => $token->getExpires()
            ],
            now()->addDays(30)
        );
    }

    /**
     * Get cached token
     */
    private function getCachedToken(): ?AccessToken
    {
        $cached = Cache::get('upwork_token');

        if (!$cached) {
            return null;
        }

        return new AccessToken($cached);
    }

    public function searchJobs(array $args = []): array
    {
        $defaults = [
            'search' => '',
            'limit' => 10,
            'offset' => 0,
        ];
        $args = array_merge($defaults, $args);
        $token = $this->getValidToken();

        $data = [];
        $data['query'] = 'query SearchJobPostings($searchExpression_eq: String!, $limit: Int!, $offset: String!) {
  marketplaceJobPostingsSearch(
    marketPlaceJobFilter: {
      searchExpression_eq: $searchExpression_eq
      pagination_eq: { first: $limit, after: $offset }
    }
    searchType: USER_JOBS_SEARCH
    sortAttributes: { field: RECENCY }
  ) {
    edges {
      ...MarketplaceJobpostingSearchEdgeFragment
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}

fragment MarketplaceJobpostingSearchEdgeFragment on MarketplaceJobpostingSearchEdge {
  node {
    id
    title
    description
    ciphertext
    duration
    durationLabel
    createdDateTime
    engagement
    amount {
      rawValue
      displayValue
    }
    recordNumber
    experienceLevel
    category
    subcategory
    freelancersToHire
    relevance {
      effectiveCandidates
      recommendedEffectiveCandidates
      uniqueImpressions
      publishTime
    }
    enterprise
    relevanceEncoded
    totalApplicants
    preferredFreelancerLocation
    preferredFreelancerLocationMandatory
    premium
    clientNotSureFields
    clientPrivateFields
    applied
    createdDateTime
    publishedDateTime
    renewedDateTime
    client {
      totalHires
      totalPostedJobs
      totalSpent {
        displayValue
      }
      verificationStatus
      location {
        country
        timezone
      }
      totalReviews
      totalFeedback
    }
    skills {
      name
      prettyName
      highlighted
    }
    occupations {
      category {
        id
        prefLabel
      }
      subCategories {
        id
        prefLabel
      }
      occupationService {
        id
        prefLabel
      }
    }
    hourlyBudgetType
    hourlyBudgetMin {
      displayValue
    }
    hourlyBudgetMax {
      displayValue
    }
    totalFreelancersToHire
  }
}
';
        $data['variables'] = [
            'searchExpression_eq' => $args['search'],
            'limit' => $args['limit'],
            'offset' => $args['offset'],
        ];

        try {
            $request = $this->provider->createGraphQLRequest($data, $token->getToken());
            $response = $this->provider->getParsedResponse($request);

            $searchResult = $response['data']['marketplaceJobPostingsSearch'];

            return [
                'jobs' => array_map(function($edge) {
                    return $edge['node'];
                }, $searchResult['edges']),
                'has_next_page' => $searchResult['pageInfo']['hasNextPage'],
                'end_cursor' => $searchResult['pageInfo']['endCursor']
            ];

        } catch (\Exception $e) {
            Log::error('Upwork API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCategories()
    {
        $token = $this->getValidToken();
        $data = [
            'query' => '
  query ontologyCategories {
    ontologyCategories {
      id
      preferredLabel
      subcategories {
        id
        preferredLabel
      }
    }
  }'
        ];

        try {
            $request = $this->provider->createGraphQLRequest($data, $token->getToken());
            $response = $this->provider->getParsedResponse($request);
        } catch (\Exception $e) {

        }

        return $response['data']['ontologyCategories'];
    }

}
