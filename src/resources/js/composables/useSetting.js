export const settingNames = Object.freeze({
    AI: 'Ai',
})

export const settingOptionValueTypes = Object.freeze({
    TEXT: 'text',
    STRING: 'string',
    BOOLEAN: 'boolean',
    INTEGER: 'integer',
    FLOAT: 'float',
    DECIMAL: 'decimal',
    JSON: 'json',
    ARRAY: 'array',
    URL: 'url',
    IMAGE: 'image',
    COLOR: 'color',
})

export const settingOptions = Object.freeze({
    OPTION_AI_BRAIN: 'ai-brain-id',
})

export const formatSettingValue = (value) => {
    if (value === null || value === undefined) {
        return ''
    }
    if (typeof value !== 'object') {
        return String(value)
    }
    return JSON.stringify(value, null, 2)
}

export function useSetting() {
    const settingOptionValueTypeOptions = Object.values(settingOptionValueTypes)

    const isEmpty = (value) => {
        return value === null ||
            value === undefined ||
            (typeof value === 'string' && value.trim() === '')
    }

    const hasValue = (value) => {
        return value !== null &&
            value !== undefined &&
            value !== ''
    }

    const isTruthyValue = (value) => {
        return value === true ||
            value === 1 ||
            value === '1' ||
            String(value).toLowerCase() === 'true' ||
            String(value).toLowerCase() === 'yes' ||
            String(value).toLowerCase() === 'on'
    }

    const getDefaultValueByType = (type) => {
        if (type === settingOptionValueTypes.BOOLEAN) {
            return false
        }
        if (type === settingOptionValueTypes.ARRAY) {
            return []
        }
        if (type === settingOptionValueTypes.JSON) {
            return {}
        }
        if (type === settingOptionValueTypes.COLOR) {
            return '#000000'
        }
        return null
    }

    return {
        settingOptionValueTypes,
        settingOptionValueTypeOptions,
        settingOptions,
        settingNames,
        isEmpty,
        hasValue,
        isTruthyValue,
        getDefaultValueByType,
        formatSettingValue,
    }
}
