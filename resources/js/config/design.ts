const severities = ['success', 'info', 'warning', 'error', 'secondary', 'contrast'];
const positions = ['left', 'right', 'top', 'bottom'];
const sizes = ['small', 'medium', 'large'];

export const design = {
    getSeverities: () => severities,

    validSeverity: (value: string) => severities.includes(value),

    getPositions: () => positions,

    validPosition: (value: string) => positions.includes(value),

    getSizes: () => sizes,

    validSize: (value: string) => sizes.includes(value),
}
