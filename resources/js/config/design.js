const severities = ['success', 'info', 'warning', 'error', 'secondary', 'contrast'];
const positions = ['left', 'right', 'top', 'bottom'];
const sizes = ['small', 'medium', 'large'];

export default {
    getSeverities: () => severities,

    validSeverity: value => severities.includes(value),

    getPositions: () => positions,

    validPosition: value => positions.includes(value),

    getSizes: () => sizes,

    validSize: value => sizes.includes(value),
}
