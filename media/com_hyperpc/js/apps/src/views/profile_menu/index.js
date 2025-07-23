/**
 * @copyright  HYPERPC
 * @author     Artem Vyshnevskiy
 */

import { createApp } from 'vue';
import ProfileMenu from './components/ProfileMenu.vue';

const node = document.getElementById('profile-menu-app');
const props = node.dataset.props ? JSON.parse(node.dataset.props) : {};

createApp(ProfileMenu, props).mount(node);
node.parentNode.replaceChild(node.firstChild, node);
