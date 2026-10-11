import { defineConfig } from 'vitepress'

const guide = [
  { text: 'Installation', link: '/guide/installation' },
  { text: 'Getting started', link: '/guide/getting-started' },
  { text: 'Configuration and tags', link: '/guide/helper' },
  { text: 'Social metadata', link: '/guide/social' },
  { text: 'Structured data', link: '/guide/structured-data' },
  { text: 'Upgrading', link: '/guide/upgrading' },
  { text: 'Contributing', link: '/guide/contributing' },
]

export default defineConfig({
  title: 'CakePHP Meta',
  description: 'HTML metadata, social preview tags, and JSON-LD for CakePHP views.',
  base: '/cakephp-meta/',
  cleanUrls: true,
  srcExclude: ['README.md', 'Install.md', 'MetaHelper.md', 'Contributing.md'],
  head: [['link', { rel: 'icon', href: '/cakephp-meta/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/guide/helper', activeMatch: '/guide/(helper|social|structured-data)' },
    ],
    sidebar: [{ text: 'Meta plugin', items: guide }],
    search: { provider: 'local' },
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-meta' }],
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-meta/edit/master/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
