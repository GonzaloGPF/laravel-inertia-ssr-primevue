import twColors from 'tailwindcss/colors'

const brand = '#1F8A7E'
const secondary = '#ecc94b'
const accent = twColors.yellow['300']
const info = twColors.blue['300']
const success = twColors.green['300']
const warning = twColors.yellow['300']
const danger = twColors.red['300']
const error = danger

const user = twColors.cyan['300']

export const colors = {
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
  email: twColors.blue['400'],

  role_admin: brand,
  role_user: info,
}
