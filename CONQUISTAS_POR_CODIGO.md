# Atribuição de Conquistas por Código

## 📋 Visão Geral

A rota `/api/usuario-conquistas/atribuir-por-codigo` permite atribuir conquistas aos usuários usando apenas o **código** de 8 caracteres, sem precisar saber o ID numérico da conquista.

**Endpoint:** `POST /api/usuario-conquistas/atribuir-por-codigo`

---

## 🎯 Por Que Usar Código em Vez de ID?

| Atribuição por ID | Atribuição por Código |
|-------------------|----------------------|
| ❌ Usuário precisa saber o ID numérico | ✅ Usuário digita/escaneia código simples |
| ❌ ID não é amigável (ex: 12345) | ✅ Código é memorável (ex: DREAM2024) |
| ❌ Difícil para marketing | ✅ Perfeito para campanhas |
| ✅ Ideal para sistemas internos | ✅ Ideal para usuários finais |
| ✅ Bom para automações | ✅ Bom para gamificação |

---

## 📱 Casos de Uso Práticos

### 1. QR Code no Evento

**Cenário:** Estande de boas-vindas no evento

```
1. Admin cria conquista "Check-in Feito"
   → Sistema gera código: K9L0M1N2

2. Admin gera QR Code com URL:
   https://app.mundodream.com.br/conquista?codigo=K9L0M1N2

3. Usuário escaneia QR Code ao fazer check-in

4. App mobile chama API:
```

```bash
POST /api/usuario-conquistas/atribuir-por-codigo
Authorization: Bearer {token_usuario}
Content-Type: application/json

{
  "user_id": 123,
  "codigo": "K9L0M1N2",
  "event_id": 17
}
```

```
5. Usuário recebe notificação:
   "🎉 Parabéns! Você ganhou 10 pontos pelo check-in!"
```

---

### 2. Código Promocional em Redes Sociais

**Cenário:** Campanha no Instagram

**Post:**
```
🎁 PROMOÇÃO EXCLUSIVA! 🎁

Use o código INSTA500 no app 
para ganhar 500 pontos extras!

Válido até 31/12/2024
```

**Fluxo:**

1. Usuário abre app
2. Vai em "Resgatar Código"
3. Digita: `INSTA500`
4. App chama API:

```bash
POST /api/usuario-conquistas/atribuir-por-codigo
{
  "user_id": 456,
  "codigo": "INSTA500",
  "event_id": 17
}
```

5. App mostra: "✅ 500 pontos adicionados!"

---

### 3. Caça ao Tesouro no Evento

**Cenário:** Jogo de exploração

```
1. Admin cria 10 conquistas:
   - "Encontrou o Cosplayer Pikachu" → ABC12345
   - "Visitou Estande da Nintendo" → DEF67890
   - "Conheceu Convidado VIP" → GHI11111
   ... e mais 7 conquistas

2. Códigos são espalhados pelo evento:
   - Cartazes com QR Codes
   - Brindes com códigos impressos
   - Staff distribuindo códigos secretos

3. Usuários coletam códigos durante o dia

4. Cada código vale pontos diferentes:
   - Bronze: 10 pontos
   - Prata: 25 pontos
   - Ouro: 50 pontos

5. Quem coletar mais pontos ganha prêmios!
```

---

### 4. Email Marketing Personalizado

**Cenário:** Recompensa por compra de ingresso

```
1. Sistema detecta nova compra de ingresso

2. Script cria conquista personalizada:
```

```sql
INSERT INTO conquistas (event_id, codigo, nome_conquista, pontos, nivel, status, created_at, updated_at)
VALUES (17, 'VIP789AB', 'Ingresso VIP Adquirido', 100, 'OURO', 'ATIVA', NOW(), NOW());
```

```
3. Sistema envia email:
```

```html
Olá João!

Obrigado por adquirir seu ingresso VIP! 🎟️

Use o código abaixo no app para ganhar 100 pontos extras:

┌─────────────┐
│  VIP789AB   │
└─────────────┘

[RESGATAR AGORA]
```

```
4. Ao clicar, redireciona para app que chama API automaticamente
```

---

### 5. Sistema de Fidelidade por Nível

**Cenário:** Programa de recompensas progressivo

```javascript
// Frontend - Sistema de níveis
const niveis = [
  { nome: 'Iniciante', codigo: 'INIT0001', pontos: 10 },
  { nome: 'Explorador', codigo: 'EXPL0002', pontos: 25 },
  { nome: 'Veterano', codigo: 'VETE0003', pontos: 50 },
  { nome: 'Lendário', codigo: 'LEGE0004', pontos: 100 }
];

async function atribuirNivel(userId, nivel) {
  const response = await fetch('/api/usuario-conquistas/atribuir-por-codigo', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      user_id: userId,
      codigo: nivel.codigo,
      event_id: 17
    })
  });
  
  if (response.ok) {
    showNotification(`🏆 Você alcançou o nível ${nivel.nome}!`);
  }
}
```

---

## 🔒 Validações Automáticas

A rota `/atribuir-por-codigo` realiza **6 validações** automáticas:

### ✅ 1. Código Existe?

```json
// Código não encontrado
{
  "success": false,
  "message": "Conquista não encontrada com o código fornecido"
}
```

### ✅ 2. Conquista Está Ativa?

```json
// Conquista desativada pelo admin
{
  "success": false,
  "message": "Conquista não está ativa",
  "status_conquista": "INATIVA"
}
```

### ✅ 3. Pertence ao Evento Correto?

```json
// Código de outro evento
{
  "success": false,
  "message": "Conquista não pertence ao evento informado",
  "event_id_conquista": 15,
  "event_id_informado": 17
}
```

### ✅ 4. Usuário Já Possui?

```json
// Já resgatou
{
  "success": false,
  "message": "Usuário já possui esta conquista neste evento"
}
```

### ✅ 5. Usuário Existe?

```json
// User ID inválido
{
  "success": false,
  "message": "Usuário não encontrado"
}
```

### ✅ 6. Dados Válidos?

```json
// Faltando campos
{
  "success": false,
  "message": "Campo codigo é obrigatório"
}
```

---

## 📊 Exemplo Completo de Integração

### Frontend React/React Native

```javascript
// components/RedeemCodeModal.jsx
import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';

export function RedeemCodeModal({ eventId }) {
  const [codigo, setCodigo] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const { user, token } = useAuth();

  const handleRedeem = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/usuario-conquistas/atribuir-por-codigo', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          user_id: user.id,
          codigo: codigo.toUpperCase(),
          event_id: eventId
        })
      });

      const data = await response.json();

      if (data.success) {
        showSuccessNotification({
          title: '🎉 Conquista Desbloqueada!',
          message: `${data.conquista.nome_conquista} - ${data.conquista.pontos} pontos`,
          description: data.conquista.descricao
        });
        
        // Atualiza saldo de pontos
        updateUserPoints(data.data.pontos_atualizados);
        
        // Fecha modal
        onClose();
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Erro ao resgatar código. Tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal>
      <h2>Resgatar Código</h2>
      <Input
        value={codigo}
        onChange={(e) => setCodigo(e.target.value)}
        placeholder="Digite o código (ex: ABC12345)"
        maxLength={8}
        autoCapitalize="characters"
      />
      {error && <ErrorMessage>{error}</ErrorMessage>}
      <Button onClick={handleRedeem} loading={loading}>
        Resgatar
      </Button>
    </Modal>
  );
}
```

### Backend PHP - Gerador de QR Code

```php
<?php
// controllers/ConquistaQRCodeController.php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ConquistaQRCodeController extends BaseController
{
    public function gerarQRCode($conquista_id)
    {
        // Busca conquista
        $conquistaModel = new ConquistaModel();
        $conquista = $conquistaModel->find($conquista_id);
        
        if (!$conquista) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Conquista não encontrada'
            ])->setStatusCode(404);
        }
        
        // Monta URL
        $url = base_url("app/conquista?codigo={$conquista->codigo}&event_id={$conquista->event_id}");
        
        // Gera QR Code
        $qrCode = QrCode::create($url)
            ->setSize(300)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Salva arquivo
        $filename = "qrcode_conquista_{$conquista->id}.png";
        $result->saveToFile(WRITEPATH . 'uploads/qrcodes/' . $filename);
        
        return $this->response->setJSON([
            'success' => true,
            'qrcode_url' => base_url('uploads/qrcodes/' . $filename),
            'conquista' => [
                'id' => $conquista->id,
                'codigo' => $conquista->codigo,
                'nome_conquista' => $conquista->nome_conquista
            ]
        ]);
    }
}
```

---

## 🎨 Templates de Mensagens

### Sucesso - Conquista Atribuída

```json
{
  "title": "🎉 Conquista Desbloqueada!",
  "message": "Comprou Ingresso",
  "description": "Adquiriu ingresso para o evento",
  "points": "+15 pontos",
  "total": "Você tem agora 65 pontos",
  "badge_image": "https://cdn.app.com/badges/bronze.png"
}
```

### Erro - Código Inválido

```json
{
  "title": "❌ Código Inválido",
  "message": "O código informado não foi encontrado",
  "suggestion": "Verifique se digitou corretamente"
}
```

### Erro - Já Resgatado

```json
{
  "title": "⚠️ Você já possui esta conquista",
  "message": "Este código já foi resgatado anteriormente",
  "suggestion": "Explore o evento para encontrar novos códigos!"
}
```

---

## 📈 Métricas e Analytics

### Queries Úteis

**Códigos mais resgatados:**
```sql
SELECT 
    c.codigo,
    c.nome_conquista,
    c.pontos,
    COUNT(uc.id) as total_resgates
FROM conquistas c
LEFT JOIN usuario_conquistas uc ON c.id = uc.conquista_id
WHERE c.event_id = 17
GROUP BY c.id
ORDER BY total_resgates DESC
LIMIT 10;
```

**Taxa de conversão de códigos:**
```sql
SELECT 
    (COUNT(DISTINCT uc.conquista_id) * 100.0 / COUNT(DISTINCT c.id)) as taxa_conversao
FROM conquistas c
LEFT JOIN usuario_conquistas uc ON c.id = uc.conquista_id
WHERE c.event_id = 17;
```

**Usuários mais ativos:**
```sql
SELECT 
    u.id,
    u.name,
    COUNT(uc.id) as total_conquistas,
    SUM(uc.pontos) as total_pontos
FROM usuarios u
INNER JOIN usuario_conquistas uc ON u.id = uc.user_id
WHERE uc.event_id = 17 AND uc.admin = 0
GROUP BY u.id
ORDER BY total_conquistas DESC
LIMIT 20;
```

---

## 🔐 Segurança

### Boas Práticas

1. **Rate Limiting**: Limite tentativas por usuário
```php
// Máximo 10 tentativas por minuto
$routes->post('atribuir-por-codigo', 'Api\UsuarioConquistas::atribuirPorCodigo', [
    'filter' => 'throttle:10,60'
]);
```

2. **Códigos Únicos**: Sistema já garante via índice único

3. **Validação de Evento**: Sempre valide se a conquista pertence ao evento

4. **Log de Tentativas**: Registre tentativas falhas para detectar abusos

5. **Expiração de Códigos**: Considere adicionar campo `expires_at`

---

## 💡 Dicas Finais

1. **Mantenha códigos simples**: 8 caracteres são suficientes
2. **Use códigos memoráveis**: Para campanhas, use palavras (ex: DREAM2024)
3. **Teste QR Codes**: Certifique-se que scanners funcionam
4. **Monitore uso**: Acompanhe quais códigos são mais populares
5. **Crie escassez**: Códigos limitados aumentam engajamento
6. **Gamifique**: Códigos secretos são mais divertidos que pontos diretos

---

## 📚 Documentação Relacionada

- [API_USUARIO_CONQUISTAS_DOCUMENTATION.md](./API_USUARIO_CONQUISTAS_DOCUMENTATION.md)
- [EXEMPLO_FLUXO_CONQUISTAS.md](./EXEMPLO_FLUXO_CONQUISTAS.md)
- [CODIGO_CONQUISTAS.md](./CODIGO_CONQUISTAS.md)

