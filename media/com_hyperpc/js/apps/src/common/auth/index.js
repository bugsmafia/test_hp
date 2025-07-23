/**
 * @copyright  HYPERPC
 * @author     Sergey Voronin
 */

import { createApp } from 'vue';
import AuthForm from "./components/AuthForm2.vue"

const components = [
    {
        selector: '.vueAuthForm',
        component: AuthForm,
    },
];

components.forEach(component => {
    let componentNodes = document.querySelectorAll(component.selector);

    componentNodes.forEach(node => {
        let props = node.dataset.props ? JSON.parse(node.dataset.props) : {};
        createApp(component.component, props).mount(node);
        node.parentNode.replaceChild(node.firstChild, node);
    });
});
