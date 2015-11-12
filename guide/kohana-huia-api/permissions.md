### Permissões
Todos os modelos vem como leitura habilitada e gravação bloqueada. Os dois únicos que vem com gravação bloqueada são ´user´ e ´logs´.

- *write* permite que grave um objeto.
- *read* permite ler um item pelo seu ID.
- *list* permite listar todos os itens, caso queira que o usuário consiga ler um dado específico mas não visualizar todos os itens cadastrados.
- *query* viabiliza a execução de queries via interface.

~~~
  'permissions' => array(
    'write' => FALSE,
    'read' => TRUE,
    'list' => TRUE,

    'query' => TRUE,
  ),
~~~
