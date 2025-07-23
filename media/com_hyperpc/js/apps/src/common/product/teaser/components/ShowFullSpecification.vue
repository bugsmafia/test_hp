<template>
    <div
        class="tm-product-teaser__show-specification tm-padding-4 uk-padding-remove-horizontal"
        @click="showSpecification"
    >
        <div class="uk-position-relative uk-flex uk-flex-center uk-flex-middle">
            <hr class="uk-width-1-1"/>
            <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="uk-position-absolute"
            >
                <circle cx="12" cy="12" r="12" fill="#C0FF01"/>
                <path
                    d="M12 7V17"
                    stroke="#141414"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path
                    d="M7 12H17"
                    stroke="#141414"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>
        <div
            class="tm-product-teaser__show-specification-link tm-text-size-14 tm-padding-4-top uk-text-center"
        >
            {{text.showFullSpecification}}
        </div>
    </div>
</template>

<script>

export default {
    name: "ShowFullSpecification",
    props: {
        itemKey: {
            type: String,
            required: true,
        },
        title: {
            type: String,
            default: "",
        },
        linkText: {
            type: String,
            default: 'Показать всю спецификацию'
        }
    },
    data() {
        return {
            specsHtml: null,
            text: {
                showFullSpecification: Joomla.Text._('COM_HYPERPC_PRODUCT_SHOW_FULL_SPECIFICATION', 'Show full specification'),
                specification: Joomla.Text._('COM_HYPERPC_SPECIFICATION', 'Specification'),
            }
        };
    },
    computed: {
        isMobileDevice() {
            return document.body.classList.contains('device-mobile-yes');
        },
        modalTitle() {
            return this.title ? `<div class="uk-h3 tm-padding-40-right">${this.text.specification} ${this.title}</div>` : '';
        },
        containerHtml() {
            const modalContent = `
                <div class="uk-margin-auto uk-container-small">
                    ${this.modalTitle}
                    <div class="jsFullSpecs">${this.specsHtml || '<div class="uk-text-center"><span data-uk-spinner></span></div>'}</div>
                </div>
            `;

            if (this.isMobileDevice) {
                return `<div class="jsSpecificationModal uk-modal-container uk-modal uk-flex-top" data-uk-modal="stack: true">
                     <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical uk-margin-remove-bottom uk-margin-auto-bottom@s uk-overflow-auto">
                        <button class="uk-modal-close-default" type="button" data-uk-close></button>
                        ${modalContent}
                    </div>
                </div>`;
            } else {
                return `<div class="jsSpecificationModal uk-offcanvas uk-offcanvas-flip" data-uk-offcanvas="mode: none; flip: true; overlay: true;">
                    <div class="uk-offcanvas-bar uk-width-2xlarge">
                        <button class="uk-offcanvas-close uk-icon" type="button" data-uk-close></button>
                         ${modalContent}
                    </div>
                </div>`;
            }
        }
    },
    methods: {
        async showSpecification(e) {
            e.preventDefault();
            this.removeModal();
            this.openModal();
            if (!this.specsHtml) {
                await this.fetchSpecification();
            }
        },
        async fetchSpecification() {
            try {
                const params = new URLSearchParams({
                    tmpl: 'component',
                    option: 'com_hyperpc',
                    task: 'moysklad_product.get-specification-html',
                    item_key: this.itemKey,
                });
                const ajaxBase = Joomla.getOptions('ajaxBase', '/index.php');
                const response = await fetch(`${ajaxBase}?${params}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(res.statusText || 'Ошибка сервера')
                        }
                        return res.json();
                    })

                if (!response.result) {
                    UIkit.notification(response.message || 'Ошибка сервера', 'danger');
                    throw new Error(response.message || 'Ошибка сервера');
                }
                this.specsHtml = response.html.replaceAll(' class="uk-modal-container"', '');
                this.updateModalContent();
            } catch (error) {
                UIkit.notification(error.message || 'Connection error', 'danger');
                this.hideModal();
            }
        },
        updateModalContent() {
            const tableContainer = document.querySelector('.jsFullSpecs');
            tableContainer.innerHTML = this.specsHtml;
        },
        removeModal() {
            const modal = document.querySelector('.jsSpecificationModal');
            if (modal) {
                document.body.removeChild(modal);
            }
        },
        createModalElementFromHtml() {
            const htmlString = this.containerHtml;
            const range = document.createRange();
            const fragment = range.createContextualFragment(htmlString);
            return fragment.firstElementChild;
        },
        openModal() {
            const modalElement = this.createModalElementFromHtml();
            document.body.appendChild(modalElement);
            this.isMobileDevice
                ? UIkit.modal(modalElement).show()
                : UIkit.offcanvas(modalElement).show();
        },
        hideModal() {
            const modal = document.querySelector('.jsSpecificationModal');
            if (modal) {
                this.isMobileDevice ? UIkit.modal(modal).hide() : UIkit.offcanvas(modal).hide();
            }
        },
    },
};
</script>