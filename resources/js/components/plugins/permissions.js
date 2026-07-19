import { reactive } from 'vue'

const state = reactive({
    roleName: null,
    permissions: [], // [{ route_name, create, read, update, delete, access_page }, ...]
})

export function setCurrentUserPermissions(user) {
    state.roleName = user?.role?.name ?? null
    state.permissions = user?.role?.permissions ?? []
}

export function clearCurrentUserPermissions() {
    state.roleName = null
    state.permissions = []
}

function findPermission(routeName) {
    return state.permissions.find(p => p.route_name === routeName)
}

export function isSuperadmin() {
    return state.roleName === 'Superadmin'
}

export function canAccessPage(routeName) {
    if (isSuperadmin()) return true
    return !!findPermission(routeName)?.access_page
}

export function can(action, routeName) {
    if (isSuperadmin()) return true
    return !!findPermission(routeName)?.[action]
}

export function currentRoleName() {
    return state.roleName
}
