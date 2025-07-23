/**
 * @copyright  HYPERPC
 * @author     Artem Vyshnevskiy
 */

import { createApp } from 'vue';
import Fps from "./components/Fps.vue";
import ShowFullSpecification from "./components/ShowFullSpecification.vue";

const components = [
    {
        selector: '.vueProductTeaserFps',
        component: Fps,
    },
    {
        selector: '.jsShowFullSpecification',
        component: ShowFullSpecification
    }
];

function mountComponents() {
    components.forEach(component => {
        let componentNodes = document.querySelectorAll(component.selector);

        componentNodes.forEach(node => {
            let props = node.dataset.props ? JSON.parse(node.dataset.props) : {};
            createApp(component.component, props).mount(node);
            node.parentNode.replaceChild(node.firstChild, node);
        });
    });
}

mountComponents();

document.addEventListener('hpproductsupdated', () => {
    mountComponents();
});