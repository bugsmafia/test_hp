<!--
HYPERPC - The shop of powerful computers.

This file is part of the HYPERPC package.
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@package    HYPERPC
@license    Proprietary
@copyright  Proprietary https://hyperpc.ru/license
@link       https://github.com/HYPER-PC/HYPERPC".

@author     Artem Vyshnevskiy
@author     Sergey Voronin
-->

<template>
    <div class="uk-display-inline-block uk-position-relative">
        <svg width="48" height="48" viewBox="0 0 96 96">
            <defs>
                <mask :id="maskId">
                    <rect x="0" y="0" width="96" height="96" fill="#fff"></rect>
                    <path d="M18,76 A40,40 0 1 1 78,76" :class="$style.headOutline" fill="#fff" stroke="#000" stroke-width="24" stroke-dasharray="0,184" :stroke-dashoffset="this.strokeDashOffset"></path>
                </mask>
                <linearGradient :id="gradientId" :class="$style.gradient" x1="0" x2="96" y1="75" y2="0" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="gold"></stop>
                    <stop offset="0.4" stop-color="#c0ff01"></stop>
                </linearGradient>
            </defs>
            <path d="M18,76 A40,40 0 1 1 78,76" :class="$style.path" fill="none" stroke-width="8" stroke="#83905a" :mask="`url(#${maskId})`"></path>
            <path d="M18,76 A40,40 0 1 1 78,76" :class="$style.tail" fill="none" stroke-width="8" :stroke="`url(#${gradientId})`" stroke-dasharray="184" :stroke-dashoffset="this.strokeDashOffset" :mask="`url(#${maskId})`"></path>
            <path d="M18,76 A40,40 0 1 1 78,76" :class="$style.head" fill="none" stroke-width="16" stroke="#c0ff01" stroke-dasharray="0,184" :stroke-dashoffset="this.strokeDashOffset"></path>
            <text x="50%" y="58" :class="$style.text" font-size="30px" text-anchor="middle" font-weight="600" letter-spacing="-1">{{ fpsValue }}</text>
            <text x="50%" y="90" :class="$style.text" font-size="22px" text-anchor="middle" font-weight="500">FPS</text>
        </svg>
    </div>
</template>

<script>
export default {
    props: {
        fps: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            fpsValue: this.fps
        }
    },
    computed: {
        strokeDashOffset() {
            const topLimit = 240,
                  pathLength = 184,
                  endOffset = 4,
                  percents = Math.min(100, this.fpsValue / (topLimit * 0.01)),
                  dashOffset = (pathLength / 100) * (100 - percents);

            return Math.max(endOffset, Math.min(pathLength - endOffset, dashOffset));
        },
        uniqueId() {
            return String(this.fpsValue) + Math.round(Math.random() * 100000);
        },
        maskId() {
            return 'outline-' + this.uniqueId;
        },
        gradientId() {
            return 'gradient-' + this.uniqueId;
        }
    }
};
</script>

<style module>
    .text { fill: var(--tm-color-white) }

    .path,
    .tail,
    .head,
    .headOutline {
        transition: stroke-dashoffset .3s linear;
        stroke-linecap: round;
    }

    .path { stroke: var(--tm-color-lime-600) }
    .head { stroke: var(--tm-color-lime) }

    .gradient stop:first-child { stop-color: var(--tm-color-orange) }
    .gradient stop:last-child { stop-color: var(--tm-color-lime) }
</style>
