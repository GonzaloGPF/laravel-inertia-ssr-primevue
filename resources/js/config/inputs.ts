import type { Component } from 'vue'
import iAutoComplete from '@/Components/Inputs/iAutoComplete.vue'
import iBoolean from '@/Components/Inputs/iBoolean.vue'
import iCheck from '@/Components/Inputs/iCheck.vue'
import iDate from '@/Components/Inputs/iDate.vue'
import iNumber from '@/Components/Inputs/iNumber.vue'
import iNumberRange from '@/Components/Inputs/iNumberRange.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import type { FilterFieldInput } from '@/types/filter'

/** Input components, by the name used to ask for them (i.e. in the filters). */
export const inputs: Record<FilterFieldInput, Component> = {
  text: iText,
  select: iSelect,
  auto: iAutoComplete,
  boolean: iBoolean,
  check: iCheck,
  number: iNumber,
  number_range: iNumberRange,
  date: iDate,
}
