import colors from 'tailwindcss/colors'

const brand = '#1F8A7E'
const secondary = '#ecc94b'
const accent = colors.yellow['300']
const info = colors.blue['300']
const success = colors.green['300']
const warning = colors.yellow['300']
const danger = colors.red['300']
const error = danger

const user = colors.cyan['300']

const Colors = {
  primary: brand,
  secondary,
  accent,
  success,
  warning,
  info,
  danger,
  error,
  brand,
  user,
  email: colors.blue['400'],

  role_admin: brand,
  role_user: info,
}

export default Colors
