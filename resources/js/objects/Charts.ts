import type { ChartData, ChartOptions } from 'chart.js'
import type { ChartSeries, ChartTheme, ChartType } from '@/types/chart'

const colorOf = (theme: ChartTheme, index: number) =>
  theme.series[index % theme.series.length]

/**
 * What the three kinds of chart of the project have in common is here, so they are all alike: the colors come from
 * the theme, the series (or slices) go in the order of the palette, and the tooltips and legends are the same.
 */
export const Charts = {
  /**
   * There is nothing to draw: no points, or a pie with no slices.
   */
  isEmpty(type: ChartType, labels: string[], series: ChartSeries[]): boolean {
    if (!labels.length || !series.length) return true

    return type === 'pie' && !series[0].data.some((value) => value > 0)
  },

  data(
    type: ChartType,
    labels: string[],
    series: ChartSeries[],
    theme: ChartTheme
  ): ChartData {
    if (type === 'pie') {
      const [slices] = series

      return {
        labels,
        datasets: [
          {
            label: slices.label,
            data: slices.data,
            backgroundColor: labels.map(
              (_, index) => colorOf(theme, index).color
            ),
            borderColor: theme.surface, // The slices are told apart by a line of the color of the card
            borderWidth: 2,
          },
        ],
      }
    }

    return {
      labels,
      datasets: series.map(({ label, data }, index) => {
        const { color, fill } = colorOf(theme, index)

        return type === 'line'
          ? {
              label,
              data,
              borderColor: color,
              backgroundColor: fill,
              pointBackgroundColor: color,
              fill: series.length === 1, // Several areas on top of each other are a mess
              tension: 0.35,
              pointRadius: 3,
              pointHoverRadius: 5,
            }
          : {
              label,
              data,
              backgroundColor: color,
              borderRadius: 4,
              maxBarThickness: 48,
            }
      }),
    } as ChartData
  },

  options(
    type: ChartType,
    series: ChartSeries[],
    theme: ChartTheme
  ): ChartOptions {
    const legend = {
      display: type === 'pie' || series.length > 1, // A single series is already named by the card
      position: 'bottom' as const,
      labels: { color: theme.text, usePointStyle: true },
    }
    const common = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend },
    }

    if (type === 'pie') return common

    const integers = series.every(({ data }) => data.every(Number.isInteger))

    return {
      ...common,
      scales: {
        x: {
          ticks: { color: theme.muted },
          grid: { display: false },
        },
        y: {
          beginAtZero: true,
          // Counts are not split in halves
          ticks: { color: theme.muted, ...(integers && { precision: 0 }) },
          grid: { color: theme.grid },
        },
      },
    }
  },

  /**
   * The values of the chart as a text, for the people that cannot see the canvas (a screen reader reads it).
   */
  description(
    type: ChartType,
    title: string,
    labels: string[],
    series: ChartSeries[]
  ): string {
    return [
      title,
      ...(type === 'pie' ? series.slice(0, 1) : series).map(
        ({ label, data }) =>
          `${label} - ${labels.map((name, index) => `${name}: ${data[index]}`).join(', ')}`
      ),
    ].join('. ')
  },
}
