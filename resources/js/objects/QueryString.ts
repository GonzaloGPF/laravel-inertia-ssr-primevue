const LIST_KEY = /\[\d*\]$/ // `key[]` and `key[0]`

export type SearchValues = Record<string, string | string[]>

export const QueryString = {
  parse: (queryString?: string) => {
    if (!queryString) {
      return {}
    }
    return JSON.parse(queryString)
  },
  stringify: (object: object = {}) => {
    return JSON.stringify(object)
    // return qs.stringify(JSON.parse(JSON.stringify(object)), {encode: false, arrayFormat: 'brackets'})
  },

  /**
   * Parses the search of an url (`?a=1&b[]=x&b[]=y`) keeping the lists as arrays,
   * the way Laravel reads them (`b[]` and `b[0]`). A repeated key is also a list.
   */
  parseSearch: (search?: string): SearchValues => {
    const values: SearchValues = {}

    new URLSearchParams(search).forEach((value, rawKey) => {
      const isList = LIST_KEY.test(rawKey)
      const key = rawKey.replace(LIST_KEY, '')
      const current = values[key]

      if (current === undefined && !isList) {
        values[key] = value
      } else {
        values[key] = [
          ...(current === undefined ? [] : [current].flat()),
          value,
        ]
      }
    })

    return values
  },

  /**
   * Builds the search of an url (without the `?`) from the given values.
   * The lists are written the way Laravel reads them: `key[]=x&key[]=y`.
   */
  toSearch: (values: SearchValues = {}): string => {
    const params = new URLSearchParams()

    Object.entries(values).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((item) => params.append(`${key}[]`, item))
      } else {
        params.append(key, value)
      }
    })

    return params.toString()
  },
}
