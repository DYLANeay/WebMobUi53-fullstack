import "./bootstrap";
import { createApp } from "vue";
import App from "./AppPollEdit.vue";

const el = document.getElementById("app");
const props = JSON.parse(el.dataset.props ?? "{}");

createApp(App, props).mount(el);
