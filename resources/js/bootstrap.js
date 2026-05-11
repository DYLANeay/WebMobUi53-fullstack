import { setDefaultHeaders, setDefaultBaseUrl } from './composables/api/useFetchApi';

setDefaultBaseUrl('/api/v1');

function getXsrfToken() {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

const xsrf = getXsrfToken();
if (xsrf) setDefaultHeaders({ 'X-XSRF-TOKEN': xsrf });
