
import { fetchFromApi } from '@/composables/useApiClient'
import { apiCacheKey, apiCacheTTL, useApiCache } from '@/composables/useApiCache'

const { clearByPrefix } = useApiCache()

export const groups = {
    User: 'User',
    Genre: 'Genre',
    Language: 'Language',
    AiBrain: 'Ai Brain',
    KdpLayout: 'Kdp Layout',
    DocumentStyle: 'Document Style',
    AiPrompt: 'Ai Prompt',
    NovelGenerator: 'Novel Generator',
}

export const access = {
    ViewAny: 'View any',
    View: 'View',
    Create: 'Create',
    Update: 'Update',
    Delete: 'Delete',
    Restore: 'Restore',
    ForceDelete: 'Force delete',
}

export const getPermissions = async (authUser) => {
    if (!authUser) {
        return []
    }

    let permissions = authUser.user_permissions

    if (!Array.isArray(permissions)) {
        const apiUrl = route('search.user', { slugOrId: authUser.id, })
        const user = await fetchFromApi(
            apiUrl,
            {},
            {
                key: `${apiCacheKey.API_USER}:${authUser.id}`,
                ttl: apiCacheTTL.API_USER,
            }
        )

        permissions = user?.user_permissions || []
    }

    return permissions
}

export const clearPermissionCache = (userId) => {
    if (userId) {
        clearByPrefix(`${apiCacheKey.API_USER}:${userId}`)
        return
    }

    clearByPrefix(`${apiCacheKey.API_USER}:`)
}

export const hasPermission = async (authUser, module, permissionAccess) => {
    if (!authUser) {
        return false
    }

    if (authUser.is_super_admin) {
        return true
    }

    const permissions = await getPermissions(authUser)

    return permissions.some(
        permission =>
            permission.module ===
            module &&
            permission.access ===
            permissionAccess
    )
}

export const canAccessUser = async (authUser) => hasPermission(authUser, groups.User, access.ViewAny)
export const canViewUser = async (authUser, user) => hasPermission(authUser, groups.User, access.View)
export const canCreateUser = async (authUser) => hasPermission(authUser, groups.User, access.Create)
export const canUpdateUser = async (authUser, user) => hasPermission(authUser, groups.User, access.Update)
export const canActiveInactiveUser = async (authUser, user) => {
    if (user?.is_default) {
        return false
    }
    if (user?.deleted_at) {
        return await hasPermission(
            authUser,
            groups.User,
            access.Restore
        )
    }
    else {
        return await hasPermission(
            authUser,
            groups.User,
            access.Delete
        )
    }
}
export const canDeleteUser = async (authUser, user) => {
    if (user?.is_default) {
        return false
    }
    return await hasPermission(
        authUser,
        groups.User,
        access.ForceDelete
    )
}

export const canAccessGenre = async (authUser) => hasPermission(authUser, groups.Genre, access.View)
export const canViewGenre = async (authUser, genre) => hasPermission(authUser, groups.Genre, access.ViewAny)
export const canCreateGenre = async (authUser, genre) => hasPermission(authUser, groups.Genre, access.Create)
export const canUpdateGenre = async (authUser, genre) => hasPermission(authUser, groups.Genre, access.Update)
export const canDeleteGenre = async (authUser, genre) => hasPermission(authUser, groups.Genre, access.Delete)

export const canAccessLanguage = async (authUser) => hasPermission(authUser, groups.Language, access.View)
export const canViewLanguage = async (authUser, language) => hasPermission(authUser, groups.Language, access.ViewAny)
export const canCreateLanguage = async (authUser, language) => hasPermission(authUser, groups.Language, access.Create)
export const canUpdateLanguage = async (authUser, language) => hasPermission(authUser, groups.Language, access.Update)
export const canDeleteLanguage = async (authUser, language) => hasPermission(authUser, groups.Language, access.Delete)

export const canAccessAiBrain = async (authUser) => hasPermission(authUser, groups.AiBrain, access.View)
export const canViewAiBrain = async (authUser, aiBrain) => hasPermission(authUser, groups.AiBrain, access.ViewAny)
export const canCreateAiBrain = async (authUser, aiBrain) => hasPermission(authUser, groups.AiBrain, access.Create)
export const canUpdateAiBrain = async (authUser, aiBrain) => hasPermission(authUser, groups.AiBrain, access.Update)
export const canDeleteAiBrain = async (authUser, aiBrain) => hasPermission(authUser, groups.AiBrain, access.Delete)

export const canAccessNovelGenerator = async (authUser) => hasPermission(authUser, groups.NovelGenerator, access.View)
export const canViewNovelGenerator = async (authUser, novelGenerator) => hasPermission(authUser, groups.NovelGenerator, access.ViewAny)
export const canCreateNovelGenerator = async (authUser, novelgenerator) => hasPermission(authUser, groups.NovelGenerator, access.Create)
export const canUpdateNovelGenerator = async (authUser, novelGenerator) => hasPermission(authUser, groups.NovelGenerator, access.Update)
export const canDeleteNovelGenerator = async (authUser, novelGenerator) => hasPermission(authUser, groups.NovelGenerator, access.Delete)

export const canAccessKdpLayout = async (authUser) => hasPermission(authUser, groups.KdpLayout, access.View)
export const canViewKdpLayout = async (authUser, kdpLayout) => hasPermission(authUser, groups.KdpLayout, access.ViewAny)

export const canAccessDocumentStyle = async (authUser) => hasPermission(authUser, groups.DocumentStyle, access.View)
export const canViewDocumentStyle = async (authUser, documentStyle) => hasPermission(authUser, groups.DocumentStyle, access.ViewAny)

export const canAccessAiPrompt = async (authUser) => hasPermission(authUser, groups.AiPrompt, access.View)
export const canViewAiPrompt = async (authUser, aiPrompt) => hasPermission(authUser, groups.AiPrompt, access.ViewAny)
export const canUpdateAiPrompt = async (authUser, aiPrompt) => hasPermission(authUser, groups.AiPrompt, access.Update)

export const canAccessActivityLog = async (authUser) => true
export const canDeleteActivityLog = async (authUser) => authUser?.is_super_admin

export const canAccessQueueMonitor = (authUser) => authUser?.is_super_admin

export const canAccessLogViewer = (authUser) => authUser?.is_super_admin
