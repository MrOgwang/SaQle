
 import { defineConfig } from 'vitepress'

 export default defineConfig({
     title: 'SaQle',
     description: 'A PHP framework for building modern web applications and APIs.',

     cleanUrls: true,

     themeConfig: {
         logo: undefined,

         nav: [
             { 
                 text: 'Home', 
                 link: '/' 
             },
             { 
                 text: 'Docs', 
                 link: '/philosophy' 
             },
             {
                 text: 'GitHub',
                 link: 'https://github.com/MrOgwang/SaQle'
             }
         ],

         sidebar: [
             {
                 text: 'Introduction',
                 items: [
                     { 
                         text: 'About SaQle', 
                         link: '/about' 
                     },
                     { 
                         text: 'Philosophy', 
                         link: '/philosophy' 
                     }
                 ]
             },
             {
                 text: 'Getting Started',
                 items: [
                     {
                         text: 'Introduction',
                         link: '/getting-started/introduction'
                     },
                     {
                         text: 'Set Up Your Development Environment',
                         items: [
                             {
                                 text: 'Windows',
                                 link: '/getting-started/environment-setup/windows'
                             },
                             {
                                 text: 'Linux',
                                 link: '/getting-started/environment-setup/linux'
                             }
                         ]
                     },
                     {
                         text: 'Your First Application',
                         link: '/getting-started/getstarted'
                     },
                     {
                         text: 'Project Structure',
                         link: '/getting-started/structure'
                     },
                     {
                         text: 'Build The Application',
                         items: [


                         ]
                     }
                 ]
             },
             {
                 text: 'Fundamentals',
                 items: [
                     {
                         text: 'Application',
                         link: '/fundamentals/application'
                     },
                     {
                         text: 'Configuration',
                         link: '/fundamentals/configuration'
                     },
                     {
                         text: 'Modules',
                         link: '/fundamentals/modules'
                     },
                     {
                         text: 'Components',
                         link: '/fundamentals/components'
                     },
                     { 
                         text: 'Routing', 
                         link: '/fundamentals/routing' 
                     },
                     {
                         text: 'ORM',
                         items: [
                             {
                                 text: 'Overview',
                                 link: ''
                             },
                             {
                                 text: 'Databases',
                                 link: ''
                             },
                             {
                                 text: 'Models',
                                 link: ''
                             },
                             {
                                 text: 'Fields',
                                 link: ''
                             },
                             {
                                 text: 'Query Manager',
                                 link: ''
                             },
                             {
                                 text: 'Migrations',
                                 link: ''
                             }
                         ]
                     },
                     { 
                         text: 'Requests', 
                         link: '' 
                     },
                     { 
                         text: 'Messages', 
                         link: '' 
                     },
                     { 
                         text: 'Responses', 
                         link: '' 
                     },
                     { 
                         text: 'Middleware', 
                         link: '' 
                     },
                     { 
                         text: 'Templates', 
                         link: '' 
                     },
                     { 
                         text: 'Layouts', 
                         link: '' 
                     }
                 ]
             },
             {
                 text: 'Building Applications',
                 items: [
                     {
                         text: 'Pages',
                         link: ''
                     },
                     {
                         text: 'Forms',
                         link: ''
                     },
                     {
                         text: 'CRUD',
                         link: ''
                     },
                     {
                         text: 'Contracts',
                         link: ''
                     },
                     {
                         text: 'Authentication',
                         link: ''
                     },
                     {
                         text: 'Authorization',
                         link: ''
                     },
                     {
                         text: 'APIs',
                         link: ''
                     },
                     {
                         text: 'File Uploads',
                         link: ''
                     },
                     {
                         text: 'Modules',
                         link: ''
                     }
                 ]
             },
             {
                 text: 'Reference',
                 items: [
                     {
                         text: 'CLI',
                         link: ''
                     }
                 ]
             }
         ],

         search: {
             provider: 'local'
         },

         socialLinks: [
             {
                 icon: 'github',
                 link: 'https://github.com/MrOgwang/SaQle'
             }
         ],

         footer: {
             message: 'Built with SaQle.',
             copyright: 'Copyright © 2026 SaQle'
         },

         outline: {
             level: [2, 3],
             label: 'On this page'
         }
     },

     markdown: {
         theme: {
             light: 'github-dark',
             dark: 'github-dark'
         },
         lineNumbers: true
     }
 })
