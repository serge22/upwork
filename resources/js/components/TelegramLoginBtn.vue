<script setup lang="ts">
import type { PropType } from 'vue'
import { onMounted, ref } from 'vue'

const props = defineProps({
    mode: {
        type: String as PropType<Mode>,
        required: true,
        validator: (prop: Mode) => ['callback', 'redirect'].includes(prop),
    },
    botUsername: {
        type: String,
        required: true,
        validator: (prop: String) => prop.endsWith('bot') || prop.endsWith('Bot'),
    },
    redirectUrl: {
        type: String,
        default: '',
    },
    size: {
        type: String as PropType<Size>,
        default: 'large' as Size,
        validator: (prop: Size) => ['small', 'medium', 'large'].includes(prop),
    }
})

const emit = defineEmits(['callback'])

type Mode = 'callback' | 'redirect'
type Size = 'small' | 'medium' | 'large'

const button = ref()

const onTelegramAuth = (user: any) => {
  emit('callback', user)
}

onMounted(() => {
    const script = document.createElement('script');
    script.src = 'https://telegram.org/js/telegram-widget.js';
    script.async = true;
    script.setAttribute('data-size', props.size)
    script.setAttribute('data-telegram-login', props.botUsername)
    if (props.mode === ('callback' as Mode)) {
        (window as any).onTelegramAuth = onTelegramAuth
        script.setAttribute('data-onauth', 'window.onTelegramAuth(user)')
    }
    else {
        script.setAttribute('data-auth-url', props.redirectUrl)
    }
    document.body.appendChild(script);

    if (button.value)
        button.value.appendChild(script)
});
</script>

<template>
    <div ref="button" />
</template>
