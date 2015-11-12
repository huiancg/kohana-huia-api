### Customização de filtros
É possível definir regras específicas para os modelos acrescentando antes do tipo de query a palavra custom e utilizando o modelo como vetor e dentro dele os valores referentes.
 publicados.

~~~
    // Exemplo para retornar somente posts publicados
    'custom_filters' => array(
        'posts' => array(
            'query' => array('where', 'published', '=', TRUE),
        ),
    ),
~~~