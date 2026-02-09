# Engine de Assinaturas (MiCore)

Autor: Jeandreo Furquim  
Ultima atualizacao: 2026-02-09  

Introducao: Este guia descreve a arquitetura do sistema de assinaturas da Central,
como os pedidos, transacoes e assinaturas se conectam e como manter consistencia
e auditabilidade no ciclo de cobranca recorrente.

Este documento explica como funciona a engine de pedidos no backend da Central.

# Sistema de Assinaturas - Central (Backend)

Este documento descreve o fluxo de assinatura no backend da Central e o papel de cada entidade envolvida.

## Visao geral do fluxo

1. O cliente seleciona modulos no front (MiCore) e envia a intencao de compra.
2. A Central cria ou atualiza um Order em status `draft`.
3. A Central calcula precos e aplica regras (somente na Central).
4. O cliente pode configurar modulos com variaveis (ex: uso/tiers).
5. O cliente prossegue para o checkout e inicia o pagamento.
6. Um OrderTransaction e criado para registrar o pagamento.
7. Com pagamento confirmado, a Central cria a assinatura (ClientSubscription) e vincula ao Order.

## Entidades principais

### Order
Representa a intencao de compra e o estado do fluxo.

- Quando e criado:
  - Ao selecionar modulos pela primeira vez (Order draft).
- Status tipicos:
  - `draft` (rascunho)
  - `pending_payment` (aguardando pagamento)
  - `paid` (pago)
  - `canceled` (cancelado)
- Campos importantes:
  - `current_step` (etapa atual do fluxo)
  - `total_amount` (total calculado pela Central)
  - `pricing_snapshot` / `rules_snapshot` (congelamento de regras e precos)
  - `coupon_*` (dados do cupom aplicados no pedido)

### OrderItem
Itens do pedido (modulos, pacotes, creditos, etc).

- Quando e criado:
  - Ao montar o Order a partir dos modulos selecionados.
- Campos importantes:
  - `item_type` (module, package, etc)
  - `item_name_snapshot` (nome congelado)
  - `unit_price_snapshot` e `subtotal_amount`
  - `pricing_model_snapshot` (modelo de precificacao aplicado)

### OrderItemConfiguration
Configuracoes dinamicas por item (ex: uso mensal, volume).

- Quando e criado:
  - Ao salvar configuracoes do cliente para modulos por uso.
- Campos importantes:
  - `key` / `value`
  - `derived_pricing_effect` (impacto no preco)

### OrderTransaction
Registro de pagamento do pedido.

- Quando e criado:
  - Ao iniciar o pagamento (checkout).
- Campos importantes:
  - `gateway_id`, `external_transaction_id`
  - `status` (pending, paid, failed, etc)
  - `amount`, `currency`
  - `raw_response_snapshot` (resposta congelada do gateway)

### ClientSubscription
Assinatura recorrente gerada a partir de um Order pago.

- Quando e criado:
  - Somente apos pagamento confirmado do Order.
- Campos importantes:
  - `order_id` (referencia do pedido que originou a assinatura)
  - `status` (active, canceled, paused, etc)
  - `current_period_start` / `current_period_end`

### ClientSubscriptionItem
Modulos ativos dentro da assinatura.

- Quando e criado:
  - Ao criar a assinatura com base nos OrderItems.

## Cupons
Cupons podem ser aplicados em qualquer etapa, porem sempre no Order.

- Os dados do cupom sao congelados no pedido (snapshot) para auditoria.
- Apenas um cupom por pedido.
- Desconto e aplicado sobre o total do Order.

## Regras importantes

- Precificacao sempre e feita na Central.
- O front nunca envia valores financeiros.
- O Order e a fonte de verdade do fluxo.
- A assinatura (ClientSubscription) so nasce a partir de um Order pago.

## Resumo

O Order e a espinha dorsal do fluxo. Ele concentra:
- intencao de compra
- configuracoes
- calculo de preco
- pagamentos

A partir de um Order pago, a Central gera a assinatura e ativa os modulos.
