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
}
