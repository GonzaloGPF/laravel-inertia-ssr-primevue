const QueryString = {
    parse: (queryString) => {
        return JSON.parse(queryString)
    },
    stringify: (object) => {
        return JSON.stringify(object)
        // return qs.stringify(JSON.parse(JSON.stringify(object)), {encode: false, arrayFormat: 'brackets'})
    }
}

export default QueryString
