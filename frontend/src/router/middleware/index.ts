import guest from './rules/guest';
import requiresAuth from './rules/requiresAuth';
import permission from './rules/permission';
import role from './rules/role';
import guestOnly from './rules/guestOnly';
import roleAndPermission from './rules/roleAndPermission';

export default {
  guest,
  requiresAuth,
  permission,
  role,
  guestOnly,
  roleAndPermission
};