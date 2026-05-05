import "./bootstrap";
import { createApp } from "vue";
import AppPollVote from "./AppPollVote.vue";

const el = document.getElementById("app");
const props = JSON.parse(el.dataset.props ?? "{}");

createApp(AppPollVote, props).mount(el);
