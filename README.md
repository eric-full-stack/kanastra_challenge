# Kanastra Challange

Este é um projeto Laravel configurado para uso com o Sail, uma interface de linha de comando leve para interagir com o ambiente Docker do Laravel. Este README fornecerá instruções sobre como configurar e executar o projeto, incluindo migrações de banco de dados, comandos e execução de workers na fila `charges`.

## Abordagens Utilizadas

- **Tempo de processamento CSV:** Para ler o arquivo de 1.1M de linhas em menos de 60s separei o arquivo em batches de 5k linhas. No início tentei a abordagem de jogar cada batch para um worker, trabalhando com filas, mas o tempo de processamento foi mais alto comparado com o processamento síncrono. Então, optei por processar cada batch de forma síncrona, o que me permitiu processar o arquivo em menos de 60s.
Para inserir em batches de 5k linhas no banco de dados utilizei SQL Bulk Insert sem o uso do Eloquent, para garantir que o processamento seja o mais rápido possível. (Ganho de ~10s no processamento do arquivo).

- **Regras e Validações:** Como cada linha do csv possuem dados do customer e muitas vezes repetidas, criei uma tabela customers e deduzi que o campo government_id é um identificador único para cada customer. Então, criei uma regra no banco de dados para garantir que o government_id seja único. Porém, a verificação de duplicidade em cada tentativa de inserção de um customer é custosa, então optei por fazer a inserção usando INSERT IGNORE, que ignora a tentativa de inserção de um customer que já existe no banco de dados. O processamento dos customers são feitos a parte do processamento das charges, para garantir que os customers estejam disponíveis para serem relacionados com as charges.

- **Conclusão:** Com as abordagens acima, consegui processar o arquivo de 1.1M de linhas em média de 45s, dependendo do computador que está rodando o projeto. Apesar da abordagem de processamento síncrono ser mais rápida localmente por N motivos, creio que a abordagem por filas com vários workers em um servidor dedicado poderia ser mais eficiente e robusta, pois o processamento seria distribuído e poderia ser escalado de acordo com a demanda.


## Requisitos

- Docker
- Docker Compose

## Configuração Inicial

1. **Clone o repositório:**
   ```sh
   git clone https://github.com/eric-full-stack/kanastra_challenge
   cd kanastra_challenge
   ```

2. **`.env` já configurado para facilitar a demonstração**

3. **Instale as dependências do Composer:**
   ```sh
   ./vendor/bin/sail composer install
   ```

4. **Suba os containers Docker:**
   ```sh
   ./vendor/bin/sail up -d
   ```

## Executando Migrações

Para rodar as migrações de banco de dados, use o seguinte comando:

```sh
./vendor/bin/sail artisan migrate
```

## Executando Workers na Fila `charges`

Para executar workers na fila `charges`, utilize o comando abaixo:

```sh
./vendor/bin/sail artisan queue:work --queue=charges
```

## Executando o scheduler

Para executar o scheduler, utilize o comando abaixo:

```sh
./vendor/bin/sail artisan schedule:work
```

## Comandos Úteis

- **Parar os containers Docker:**
  ```sh
  ./vendor/bin/sail down
  ```

- **Acessar o container do aplicativo:**
  ```sh
  ./vendor/bin/sail shell
  ```

- **Executar testes:**
  ```sh
  ./vendor/bin/sail artisan test
  ```

## Problemas Comuns

- **Permissões de Arquivo:** Se você encontrar problemas de permissão ao executar comandos dentro dos containers Docker, tente alterar as permissões dos arquivos e diretórios apropriados.

- **Dependências Não Instaladas:** Certifique-se de que todas as dependências do Composer estão instaladas corretamente e o ambiente Docker está configurado corretamente.
