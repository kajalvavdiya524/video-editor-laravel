const mix = require("laravel-mix");

require("laravel-mix-polyfill");
const TargetsPlugin = require("targets-webpack-plugin");
// mix.webpackConfig({
//     plugins: [
//         new TargetsPlugin({
//           browsers: ['last 2 versions', 'chrome >= 41', 'IE 11'],
//         }),
//     ]
// });
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .setPublicPath("public")
  .setResourceRoot("../") // Turns assets paths in css relative to css file
  .sass("resources/sass/frontend/app.scss", "css/frontend.css")
  .sass("resources/sass/backend/app.scss", "css/backend.css")
  .js("resources/js/fonts.js", "js/fonts.js")
  .js("resources/js/frontend/app.js", "js/frontend.js")
  .js("resources/js/frontend/common.js", "js/common.js")
  .js("resources/js/frontend/create.js", "js/create.js")
  .js("resources/js/frontend/project_type.js", "js/project_type.js")
  .js("resources/js/frontend/create_AmazonFresh.js", "js/create_AmazonFresh.js")
  .js("resources/js/frontend/index.js", "js/index.js")
  .js("resources/js/frontend/file_image.js", "js/file_image.js")
  .js("resources/js/frontend/history.js", "js/history.js")
  .js("resources/js/frontend/project.js", "js/project.js")
  .js("resources/js/frontend/request_approve.js", "js/request_approve.js")
  .js("resources/js/frontend/uploadimg.js", "js/uploadimg.js")
  .js("resources/js/frontend/cropper.js", "js/cropper.js")
  .js("resources/js/frontend/video.js", "js/video.js")
  .js("resources/js/frontend/instagram.js", "js/instagram.js")
  .js("resources/js/frontend/kroger.js", "js/kroger.js")
  .js("resources/js/frontend/walmart.js", "js/walmart.js")
  .js("resources/js/frontend/pilot.js", "js/pilot.js")
  .js("resources/js/frontend/sam.js", "js/sam.js")
  .js("resources/js/frontend/new_template.js", "js/new_template.js")
  .js("resources/js/frontend/columns.js", "js/columns.js")
  .js("resources/js/frontend/colResizable-1.6.min.js", "js/col_resizable.js")
  .js("resources/js/frontend/group/index.js", "js/group/index.js")
  .js("resources/js/frontend/group/create.js", "js/group/create.js")
  .js("resources/js/frontend/group/edit.js", "js/group/edit.js")
  .js("resources/js/frontend/group/show.js", "js/group/show.js")
  .js(
    "resources/js/frontend/group/edit_template.js",
    "js/group/edit_template.js"
  )
  .js("resources/js/frontend/preview/instagram.js", "js/preview/instagram.js")
  .js(
    "resources/js/frontend/preview/amazonfresh.js",
    "js/preview/amazonfresh.js"
  )
  .js("resources/js/frontend/preview/kroger.js", "js/preview/kroger.js")
  .js("resources/js/frontend/preview/superama.js", "js/preview/superama.js")
  .js("resources/js/frontend/preview/walmart.js", "js/preview/walmart.js")
  .js("resources/js/frontend/preview/mrhi.js", "js/preview/mrhi.js")
  .js(
    "resources/js/frontend/preview/varietypack.js",
    "js/preview/varietypack.js"
  )
  .js(
    "resources/js/frontend/preview/nutritionfacts.js",
    "js/preview/nutritionfacts.js"
  )
  .js(
    "resources/js/frontend/preview/virtualbundle.js",
    "js/preview/virtualbundle.js"
  )
  .js("resources/js/frontend/preview/pilot.js", "js/preview/pilot.js")
  .js("resources/js/frontend/preview/sam.js", "js/preview/sam.js")
  .js(
    "resources/js/frontend/preview/new_template.js",
    "js/preview/new_template.js"
  )

  .js("resources/js/backend/app.js", "js/backend.js")
  .js("resources/js/backend/customer.js", "js/customer.js")
  .js("resources/js/backend/template.js", "js/template.js")
  .js("resources/js/backend/images.js", "js/images.js")
  .js("resources/js/backend/template/main.js", "js/template/main.js")
  .js("resources/js/backend/template/preview.js", "js/template/preview.js")
  .js("resources/js/backend/data.js", "js/data.js")
  .js("resources/js/backend/upload.js", "js/upload.js")
  .js("resources/js/backend/multicheckbox.js", "js/multicheckbox.js")
  .js("resources/js/backend/video/project.js", "js/video-project.js")
  .js("resources/js/backend/video/template.js", "js/video-template.js")
  .js(
    "resources/js/backend/video/media-cropper.js",
    "js/video-media-cropper.js"
  )
  .js("resources/js/backend/video/media-tag.js", "js/video-media-tag.js")
  .js("resources/js/backend/video/media-trim.js", "js/video-media-trim.js")
  .js("resources/js/backend/video/media.js", "js/video-media.js")
  .js("resources/js/backend/video/review.js", "js/video-review.js")
  .js("resources/js/backend/video/share.js", "js/video-share.js")
  .js("resources/js/backend/video/theme-edit.js", "js/video-theme-edit.js")
  .js("resources/js/backend/video/theme.js", "js/video-theme.js")
  .js("resources/js/backend/positioning.js", "js/positioning.js")
  .extract([
    "alpinejs",
    "jquery",
    "bootstrap",
    "popper.js",
    "axios",
    "sweetalert2",
    "lodash",
  ]);
// .sourceMaps()
// .polyfill({
//     enabled: true,
//     useBuiltIns: "usage",
//     targets: {"ie": 11},
//     debug: true,
//     corejs: 3,
//  });

if (mix.inProduction()) {
  mix.version().options({
    // Optimize JS minification process
    terser: {
      cache: true,
      parallel: true,
      sourceMap: true,
    },
  });
} else {
  // Uses inline source-maps on development
  mix.webpackConfig({
    devtool: "inline-source-map",
  });
}
