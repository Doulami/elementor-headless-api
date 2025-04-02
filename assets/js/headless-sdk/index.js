// assets/js/headless-sdk/index.js

import { warnIfInvalidBaseURL } from './utils';
import * as pages from './pages';
import * as products from './products';
import * as preview from './preview';

warnIfInvalidBaseURL();

export default {
  ...pages,
  ...products,
  ...preview,
};