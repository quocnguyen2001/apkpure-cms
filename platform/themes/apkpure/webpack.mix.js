let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/themes/' + directory
const dist = 'public/themes/' + directory

mix
    .sass(source + '/assets/sass/style.scss', dist + '/css')
    .sass(source + '/assets/sass/main.scss', dist + '/css')
    .sass(source + '/assets/sass/app-detail.scss', dist + '/css')
    .sass(source + '/assets/sass/app-versions.scss', dist + '/css')
    .js(source + '/assets/js/script.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/css/style.css', source + '/public/css')
        .copy(dist + '/css/main.css', source + '/public/css')
        .copy(dist + '/css/app-detail.css', source + '/public/css')
        .copy(dist + '/css/app-versions.css', source + '/public/css')
        .copy(dist + '/js/script.js', source + '/public/js')
}
