# JSON Data Visualisation

This module integrates with the Data Visualisation Framework module providing JSON support.

## JSONPath

JSONPath is an XPath-like expression language for filtering, flattening and extracting data. This module uses a modern
JSONPath library based on [Stefan Goessner's script](http://goessner.net/articles/JsonPath/) to help you do exactly
that.

## Expression syntax

Symbol                | Description
----------------------|-------------------------
`$`                   | The root object/element (not strictly necessary)
`@`                   | The current object/element
`.` or `[]`           | Child operator
`..`                  | Recursive descent
`*`                   | Wildcard. All child elements regardless their index.
`[,]`                 | Array indices as a set
`[start:end:step]`    | Array slice operator borrowed from ES4/Python.
`?()`                 | Filters a result set by a script expression
`()`                  | Uses the result of a script expression as the index

## Examples

JSONPath                  | Result
--------------------------|-------------------------------------
`$.store.books[*].author` | the authors of all books in the store
`$..author`                | all authors
`$.store..price`           | the price of everything in the store.
`$..books[2]`              | the third book
`$..books[(@.length-1)]`   | the last book in order.
`$..books[0,1]`            | the first two books
`$..books[:2]`             | the first two books
`$..books[?(@.isbn)]`      | filter all books with isbn number
`$..books[?(@.price<10)]`  | filter all books cheapier than 10
`$..*`                     | all elements in the data (recursively extracted)

### Real-life example

Feel free to copy and paste the example below into a [JSONPath online evaluator](http://jsonpath.com/) tool and try the
JSONPath expression syntax.

```json
{
    "books": [
        {
            "title": "Beginning JSON",
            "author": "Ben Smith",
            "price": 49.99
        },
        {
            "title": "JSON at Work",
            "author": "Tom Marrs",
            "price": 29.99
        },
        {
            "title": "Learn JSON in a DAY",
            "author": "Acodemy",
            "price": 8.99
        }
    ],
    "price range": {
        "cheap": 10.00,
        "medium": 20.00
    }
}
```

In the above example, you would use `$.books[*]` to retrieve all the books in the dataset. Alternatively,
`$.books[?(@.price>10)]` would only return books priced at $10.00 or more.

## Important notes

For Drupal, a single data record would look like this:

```json
{
    "title": "Beginning JSON",
    "author": "Ben Smith",
    "price": 49.99
}
```

With that in mind, it is important to note a couple of things:

- All the property names of your data record will be exposed to Drupal as fields (e.g. title, author and price).
- Nested properties in your data records can and will **break the visualisation**.
