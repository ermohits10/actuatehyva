const { spacing } = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');
const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
    mode: process.env.TAILWIND_COMPILE_MODE || 'jit', // either 'jit' or 'aot'
    theme: {
        extend: {
            screens: {
                'sm': '560px',
                // => @media (min-width: 560px) { ... }
                'md': '768px',
                // => @media (min-width: 768px) { ... }
                'lg': '1024px',
                // => @media (min-width: 1024px) { ... }
                'xl': '1280px',
                // => @media (min-width: 1280px) { ... }
                '2xl': '1407px',
                // => @media (min-width: 1536px) { ... }
                'max767': {'max': '767px'},
            },
            fontFamily: {
                'sans': ['DM Sans', 'Arial', 'sans-serif'],
                'serif': ['Lato', 'Arial', 'sans-serif'],
                'mono': ['Inter', 'Arial', 'sans-serif'],
            },
            colors: {
                primary: {
                    lighter: colors.blue['300'],
                    "DEFAULT": colors.blue['800'],
                    darker: colors.blue['900'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                background: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                'B40802': '#B40802',
                'F2F6FA': '#F2F6FA',
                '126366': '#126366',
                '1F2937': '#1F2937',
                '1d3a58': '#1d3a58',
                '161A21': '#161A21',
                'ECECEC': '#ECECEC',
                'F5F5F5': '#F5F5F5',
                'D30202': '#D30202',
                'deal-rgba': 'rgba(232, 152, 63, 1)',
            },
            textColor: {
                orange: colors.orange,
                red: {
                    ...colors.red,
                    "DEFAULT": colors.red['500']
                },

                primary: {
                    lighter: colors.gray['700'],
                    "DEFAULT": colors.gray['800'],
                    darker: colors.gray['900'],
                },
                secondary: {
                    lighter: colors.gray['400'],
                    "DEFAULT": colors.gray['600'],
                    darker: colors.gray['800'],
                },
            },
            backgroundColor: {
                primary: {
                    lighter: colors.blue['600'],
                    "DEFAULT": colors.blue['700'],
                    darker: colors.blue['800'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                container: {
                    lighter: '#ffffff',
                    "DEFAULT": '#fafafa',
                    darker: '#f5f5f5',
                }
            },
            borderColor: {
                primary: {
                    lighter: colors.blue['600'],
                    "DEFAULT": colors.blue['700'],
                    darker: colors.blue['800'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                container: {
                    lighter: '#f5f5f5',
                    "DEFAULT": '#e7e7e7',
                    darker: '#b6b6b6',
                }
            },
            minWidth: {
                8: spacing["8"],
                20: spacing["20"],
                40: spacing["40"],
                48: spacing["48"],
            },
            minHeight: {
                14: spacing["14"],
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh',
            },
            maxHeight: {
                '0': '0',
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh',
            },
            container: {
                center: true,
                padding: '1.5rem'
            },
            backgroundImage: {
              'deals-bg': "url('../images/deals-bg.png')",
            },
            shrink: {
                '0': '0'
            }
        },
    },
    variants: {
        extend: {
            borderWidth: ['last', 'hover', 'focus'],
            margin: ['last'],
            opacity: ['disabled'],
            backgroundColor: ['even', 'odd'],
            ringWidth: ['active']
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
    purge: {
        // Examples for excluding patterns from purge
        // options: {
        //     safelist: [/^bg-opacity-/, /^-?[mp][trblxy]?-[4,8]$/, /^text-shadow/],
        // },
        content: [
           '../../**/*.phtml',
            './src/**/*.phtml',
            // parent theme in Vendor
            '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
        ],
        safelist: [
          'md:pl-6',
          'md:border-l',
          'md:border-gray-200',
          'md:pr-6',
          'bottom-[8rem]',
          'right-[2rem]',
          'inline-block',
          '-mt-4',
          'bg-126366',
          'max-w-[320px]',
          'shrink-0'
        ]
    }
})
