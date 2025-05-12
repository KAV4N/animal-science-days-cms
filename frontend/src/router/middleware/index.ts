// src/router/middleware/index.ts
import guest from './rules/guest';
import requiresAuth from './rules/requiresAuth';
import permission from './rules/permission';
import role from './rules/role';
import guestOnly from './rules/guestOnly';
import roleAndPermission from './rules/roleAndPermission';
import requiresUnchangedPassword from './rules/requiresUnchangedPassword';
export default{
  guest,
  requiresAuth,
  permission,
  role,
  guestOnly,
  roleAndPermission,
  requiresUnchangedPassword
};