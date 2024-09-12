export const devHome = 'https://codexpert.io';

import Docs from './pages/help/Docs';
import Support from './pages/help/Support';
import Activator from './pages/license/Activator';

export const helpTabs = [
    {
        path: '/',
        label: 'Documentations',
        element: Docs
    },
    {
        path: '/about',
        label: 'Support',
        element: Support
    },
];

export const licenseTabs = [
    {
        path: '/',
        label: 'Activator',
        element: Activator
    },
];

export const externalButtons = [
    {
        id      : 'changelog',
        url     : 'https://wordpress.org/plugins/user-switcher/#developers',
        label   : 'Changelog',
    },
    {
        id      : 'community',
        url     : 'https://facebook.com/groups/codexpert.io',
        label   : 'Community',
    },
    {
        id      : 'website',
        url     : 'https://codexpert.io/',
        label   : 'Official Website',
    },
    {
        id      : 'support',
        url     : 'https://help.codexpert.io/',
        label   : 'Ask Support',
    },
];