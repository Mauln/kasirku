import globals from 'globals';

export default [
  {
    files: ['resources/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.es2022,
      },
    },
    rules: {
      'no-restricted-imports': [
        'error',
        {
          paths: [
            { name: 'react', message: 'Frontend is vanilla JS — no React' },
            { name: 'react-dom', message: 'Frontend is vanilla JS — no React' },
            { name: 'vue', message: 'Frontend is vanilla JS — no Vue' },
            { name: '@inertiajs/inertia', message: 'No Inertia' },
            { name: '@inertiajs/vue3', message: 'No Inertia' },
            { name: 'alpinejs', message: 'No Alpine' },
            { name: 'htmx.org', message: 'No HTMX' },
            { name: 'svelte', message: 'Frontend is vanilla JS — no Svelte' },
            { name: '@angular/core', message: 'Frontend is vanilla JS — no Angular' },
            { name: 'chart.js', message: 'Use Canvas API vanilla' },
            { name: 'd3', message: 'Use Canvas API vanilla' },
            { name: 'hammerjs', message: 'Use native Touch events' },
          ],
        },
      ],
      'no-restricted-syntax': [
        'error',
        {
          selector:
            "AssignmentExpression[left.property.name='innerHTML'][right.type!='Literal']",
          message:
            'Use textContent/createElement for user data — innerHTML only for trusted static HTML',
        },
      ],
      'no-eval': 'error',
      'no-implied-eval': 'error',
      'no-new-func': 'error',
      'no-console': ['warn', { allow: ['error', 'warn'] }],
      'no-var': 'error',
      'prefer-const': 'warn',
    },
  },
];
