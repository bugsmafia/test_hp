<template>
    <div class="uk-offcanvas" data-uk-offcanvas="mode: none; flip: true; overlay: true;">
        <div class="uk-offcanvas-bar uk-width-2xlarge uk-flex uk-flex-column uk-offcanvas-none">
            <button class="uk-offcanvas-close uk-icon" type="button" data-uk-close></button>
            <div class="uk-h3 tm-padding-24-right tm-padding-56-right@s uk-margin-remove-bottom">
                <slot name="title"></slot>
            </div>
            <slot name="body"></slot>
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "Offcanvas",
}
</script>

<style scoped>

</style>