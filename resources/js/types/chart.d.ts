/** The only kinds of chart there are: a statistic is shown as one of them. */
export type ChartType = 'line' | 'bar' | 'pie'

/** The values of a statistic, one for each label of the chart. A pie takes the first series only: its values are the slices. */
export type ChartSeries = {
  label: string
  data: number[]
}

export type ChartColor = {
  color: string
  /** The same color, see-through: the area under a line. */
  fill: string
}

/** The colors of the PrimeVue theme, as the canvas of a chart needs them. */
export type ChartTheme = {
  text: string
  muted: string
  grid: string
  /** What is behind the chart: the background of the card. */
  surface: string
  /** One for each series, or slice of a pie, in order. They start over when there are not enough. */
  series: ChartColor[]
}
