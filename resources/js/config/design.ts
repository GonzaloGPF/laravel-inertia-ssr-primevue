export const severities = [
  'secondary',
  'success',
  'info',
  'warn',
  'help',
  'danger',
  'contrast',
] as const
const positions = ['left', 'right', 'top', 'bottom']
const sizes = ['small', 'medium', 'large']

export const design = {
  getSeverities: () => severities,

  validSeverity: (value: string) =>
    (severities as readonly string[]).includes(value),

  getPositions: () => positions,

  validPosition: (value: string) => positions.includes(value),

  getSizes: () => sizes,

  validSize: (value: string) => sizes.includes(value),
}
