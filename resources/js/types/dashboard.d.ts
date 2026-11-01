export type DashboardStats = {
  users: number
  placeholders: number[]
}

/** The users created in a month, given as Y-m */
export type MonthlyTotal = {
  month: string
  total: number
}
