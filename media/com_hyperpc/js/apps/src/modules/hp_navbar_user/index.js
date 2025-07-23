/**
 * @copyright  HYPERPC
 * @author     Artem Vyshnevskiy
 */

import { createApp } from 'vue';
import CompareBadge from './components/CompareBadge.vue';
import CartBadge from "./components/CartBadge.vue";
import Cart from './components/Cart.vue';
import UserMenu from "./components/UserMenu.vue";
import LoadConfiguration from "../../common/components/LoadConfiguration.vue";
import NotificationDot from './components/NotificationDot.vue';

const components = [
    {
        selector: '.jsCompareBadge',
        component: CompareBadge,
    },
    {
        selector: '.jsCartBadge',
        component: CartBadge
    },
    {
        selector: '#mainnav-cart-drop',
        component: Cart
    },
    {
        selector: '#mainnav-user-drop',
        component: UserMenu
    },
    {
        selector: '#load-configuration-app',
        component: LoadConfiguration
    },
    {
        selector: '#tabbar-chat-notification',
        component: NotificationDot
    }
];

components.forEach(component => {
    let componentNodes = document.querySelectorAll(component.selector);

    componentNodes.forEach(node => {
        let props = node.dataset.props ? JSON.parse(node.dataset.props) : {};
        createApp(component.component, props).mount(node);
        node.parentNode.replaceChild(node.firstChild, node);
    });
});
