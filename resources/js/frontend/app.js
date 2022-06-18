/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// CoreUI
require("@coreui/coreui");

require("../bootstrap");
require("../plugins");

window.rememberTemplateSettings = function () {
  const customer_id = $('input[name="customer_id"]').val();
  const key = "customer_" + customer_id + "_settings";
  if (localStorage.getItem(key)) {
    var settings = JSON.parse(localStorage.getItem(key));
    const template_id = $('input[name="template_id"]').val();
    const formData = $("#adForm").serializeArray();
    const queryString = JSON.stringify(formData);
    settings["template_" + template_id] = queryString;
    settings["last_template"] = template_id;
    localStorage.setItem(key, JSON.stringify(settings));
  }
};

window.forgetTemplateSettings = function (customer_id = "") {
  
  if (customer_id == "")
    customer_id = $('input[name="customer_id"]').val();
  
  const key = "customer_" + customer_id + "_settings";
  if (localStorage.getItem(key)) {
    localStorage.removeItem(key);
  }
  if (localStorage.getItem("selected_files")) {
    localStorage.removeItem("selected_files");
  }
  
  for (var i=0 ; i<5 ; i++){
    if (localStorage.getItem("image_position_"+i)) {
      localStorage.removeItem("image_position_"+i);
    }
  }

  var name = "file_ids";
  var value = "";
  var expires = "";
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
};

import Vue from "vue";
import store from "./store";

// name is optional
import VueLodash from "vue-lodash";
import lodash from "lodash";

import { BootstrapVue, IconsPlugin } from "bootstrap-vue";

import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";
import "toastr/build/toastr.css";

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

Vue.use(VueLodash, { lodash: lodash });

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context("./", true, /\.vue$/i);
files
  .keys()
  .map((key) =>
    Vue.component(key.split("/").pop().split(".")[0], files(key).default)
  );

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
  el: "#app",
  store,
});

Vue.prototype.$myToast = {
  success: () => {
    console.log("success");
  },
  failed: () => {
    console.log("failed");
  },
};
