```mermaid
flowchart LR
    subgraph Querying
        QueryBuilder <--> BuiltQuery
    end

        subgraph Abstraction
            DatabaseAdapter <--> MysqlAdapter
            DatabaseAdapter <--> SqliteAdapter
            DatabaseAdapter <--> PostgresqlAdapter
        end

        subgraph Mapping
            EntityMapper
        end
    
        subgraph Entities
            User
            Post
            Comment
        end
    
        subgraph Caching
            CacheProvider <--> RedisProvider
            CacheProvider <--> InMemoryProvider
        end

        BuiltQuery <-.-> CacheProvider
        BuiltQuery <--> DatabaseAdapter
        EntityMapper <--> Entities
        EntityMapper <--> QueryBuilder
```

Query definition in EBNF:

```
query       = identifer , param + ;
param       = identifer | query | expression ;
expression  = value operator value ;
operator    = identifer | symbol ;
value       = identifer | expression ;
identifer   = letter , ( letter | digit | "." ) + ;
```

QueryBuilder flow:

```mermaid
flowchart
    subgraph Querying
        builder(QueryBuilder)
        query(Query)
        bound(BoundQuery)
        parameters(QueryParameters)

        subgraph QueryResult
            direction TB

            resultSet(GenericEnumerable)
            affectedRows(affected rows)
        end
    end

    subgraph Abstraction
        adapter(QueryAdapter)
        connection(DatabaseConnection)
    end

    builder --creates--> query
    query --creates--> bound
    connection --provides---> adapter
    parameters --fills--> bound
    bound --uses--> adapter
    adapter --yields--> QueryResult
```
