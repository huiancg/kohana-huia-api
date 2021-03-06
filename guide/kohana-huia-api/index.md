# O que é o Huia API
Sistema que utiliza como base o Huia Template para gerar dinâmicamente sistemas REST para manipulação de dados.
Os retornos utilizam o metodo ORM::all_as_array(), descrito no Huia Template.

# Geração da API
A API REST é gerada com base nos modelos presentes na `classes/Models` e pode ser acessada na aplicação pela url `/api/`.
Assim que criar um modelo o mesmo já estará disponível e configurável como descrito a seguir em [Queries](#queries).

## Configuração de instalação
Todas as configurações relativas estão em `application/configs/huia/api.php`.

### Queries
As configurações de queries definem direções de ordenação, operações válidas e campos que podem ser utilizados.

  - [Direções](directions.md)
  - [Operações](operations.md)
  - [Permissões](permissions.md)
  - [Filtros](filters.md)
  - [Customização](customs.md)