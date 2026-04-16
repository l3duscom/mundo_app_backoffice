# 🎫 API de Ingressos - Documentação

## Visão Geral

API para gerenciar ingressos do usuário autenticado. Todos os endpoints requerem autenticação JWT.

## 🔒 Autenticação

Todos os endpoints requerem token JWT no header:

```
Authorization: Bearer seu_token_jwt_aqui
```

Para obter o token, faça login via `/api/auth/login`.

---

## 📋 Endpoints

### 1. Listar Todos os Ingressos

Retorna todos os ingressos do usuário, separados em atuais e anteriores.

**Endpoint:** `GET /api/ingressos`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "cliente": {
      "id": 1,
      "nome": "João Silva",
      "email": "joao@exemplo.com",
      "cpf": "123.456.789-00",
      "telefone": "(11) 98765-4321"
    },
    "ingressos": {
      "atuais": [
        {
          "id": 123,
          "codigo": "ABC123XYZ",
          "nome": "João Silva",
          "email": "joao@exemplo.com",
          "cpf": "123.456.789-00",
          "status": "ativo",
          "ticket_id": 45,
          "pedido_id": 78,
          "created_at": "2024-01-15 10:30:00",
          "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
          "ticket": {
            "id": 45,
            "nome": "Ingresso VIP - Evento X",
            "descricao": "Acesso VIP com open bar",
            "data_inicio": "2024-02-01 20:00:00",
            "data_fim": "2024-02-02 04:00:00",
            "valor": "150.00"
          }
        }
      ],
      "anteriores": [
        {
          "id": 100,
          "codigo": "OLD123ABC",
          "nome": "João Silva",
          "status": "usado",
          "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
          "ticket": {
            "id": 30,
            "nome": "Ingresso Comum - Evento Y",
            "data_inicio": "2023-12-15 20:00:00",
            "data_fim": "2023-12-16 02:00:00"
          }
        }
      ],
      "total_atuais": 1,
      "total_anteriores": 1,
      "total": 2
    },
    "card": {
      "id": 5,
      "numero": "1234",
      "expiration": "2025-12-31",
      "ativo": true
    },
    "indicacoes": 3,
    "convite": "JOAO123"
  }
}
```

**Respostas de Erro:**
- `401` - Token não fornecido ou inválido
- `404` - Cliente não encontrado
- `500` - Erro interno do servidor

---

### 2. Listar Apenas Ingressos Atuais

Retorna apenas ingressos válidos (não expirados há mais de 2 dias).

**Endpoint:** `GET /api/ingressos/atuais`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "ingressos": [
      {
        "id": 123,
        "codigo": "ABC123XYZ",
        "nome": "João Silva",
        "status": "ativo",
        "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
        "ticket": {
          "id": 45,
          "nome": "Ingresso VIP - Evento X",
          "data_inicio": "2024-02-01 20:00:00",
          "data_fim": "2024-02-02 04:00:00"
        }
      }
    ],
    "total": 1
  }
}
```

---

### 3. Detalhes de um Ingresso Específico

Retorna detalhes completos de um ingresso, incluindo QR Code em base64.

**Endpoint:** `GET /api/ingressos/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Parâmetros:**
- `id` (path) - ID do ingresso

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "codigo": "ABC123XYZ",
    "nome": "João Silva",
    "email": "joao@exemplo.com",
    "cpf": "123.456.789-00",
    "status": "ativo",
    "ticket_id": 45,
    "pedido_id": 78,
    "created_at": "2024-01-15 10:30:00",
    "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...",
    "ticket": {
      "id": 45,
      "nome": "Ingresso VIP - Evento X",
      "descricao": "Acesso VIP com open bar",
      "data_inicio": "2024-02-01 20:00:00",
      "data_fim": "2024-02-02 04:00:00",
      "valor": "150.00",
      "evento_id": 10
    }
  }
}
```

**Respostas de Erro:**
- `401` - Token não fornecido ou inválido
- `403` - Ingresso não pertence ao usuário
- `404` - Ingresso não encontrado
- `500` - Erro interno do servidor

---

## 📱 Exemplos de Uso

### Exemplo 1: JavaScript/Fetch

```javascript
// Obter todos os ingressos
async function getIngressos() {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost/mundo_app/api/ingressos', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (data.success) {
    console.log('Ingressos atuais:', data.data.ingressos.atuais);
    console.log('Ingressos anteriores:', data.data.ingressos.anteriores);
    console.log('Total de indicações:', data.data.indicacoes);
  }
}

// Obter detalhes de um ingresso específico
async function getIngressoDetalhes(id) {
  const token = localStorage.getItem('token');
  
  const response = await fetch(`http://localhost/mundo_app/api/ingressos/${id}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (data.success) {
    // Exibir QR Code
    const img = document.createElement('img');
    img.src = data.data.qr_code;
    document.body.appendChild(img);
  }
}
```

### Exemplo 2: cURL

```bash
# Fazer login primeiro
TOKEN=$(curl -X POST http://localhost/mundo_app/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@exemplo.com","password":"senha123"}' \
  | jq -r '.data.token')

# Listar todos os ingressos
curl -X GET http://localhost/mundo_app/api/ingressos \
  -H "Authorization: Bearer $TOKEN" \
  | jq .

# Listar apenas ingressos atuais
curl -X GET http://localhost/mundo_app/api/ingressos/atuais \
  -H "Authorization: Bearer $TOKEN" \
  | jq .

# Obter detalhes de um ingresso
curl -X GET http://localhost/mundo_app/api/ingressos/123 \
  -H "Authorization: Bearer $TOKEN" \
  | jq .
```

### Exemplo 3: React Component

```jsx
import React, { useEffect, useState } from 'react';

function IngressosList() {
  const [ingressos, setIngressos] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchIngressos();
  }, []);

  const fetchIngressos = async () => {
    const token = localStorage.getItem('token');
    
    try {
      const response = await fetch('http://localhost/mundo_app/api/ingressos', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      const data = await response.json();
      
      if (data.success) {
        setIngressos(data.data.ingressos.atuais);
      }
    } catch (error) {
      console.error('Erro ao buscar ingressos:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <h2>Meus Ingressos</h2>
      {ingressos.length === 0 ? (
        <p>Você não possui ingressos ativos.</p>
      ) : (
        <ul>
          {ingressos.map(ingresso => (
            <li key={ingresso.id}>
              <h3>{ingresso.ticket?.nome}</h3>
              <p>Código: {ingresso.codigo}</p>
              <p>Status: {ingresso.status}</p>
              <button onClick={() => verDetalhes(ingresso.id)}>
                Ver QR Code
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
```

### Exemplo 4: Flutter/Dart

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class IngressosService {
  final String baseUrl = 'http://localhost/mundo_app/api';
  
  Future<Map<String, dynamic>> getIngressos(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/ingressos'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Erro ao buscar ingressos');
    }
  }

  Future<Map<String, dynamic>> getIngressoDetalhes(String token, int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/ingressos/$id'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Erro ao buscar detalhes do ingresso');
    }
  }
}
```

---

## 🔐 Segurança

Todas as rotas de ingressos possuem:

✅ **Autenticação JWT obrigatória**  
✅ **Rate limiting** (60 requisições/minuto)  
✅ **HTTPS obrigatório** em produção  
✅ **Validação de propriedade** (usuário só acessa seus próprios ingressos)  
✅ **Logs de auditoria**  

---

## 📊 Regras de Negócio

### Ingressos Atuais vs Anteriores

Um ingresso é considerado **anterior** quando:
- A data de término do evento (`ticket.data_fim`) passou há **mais de 2 dias**

Exemplo:
- Hoje: 2024-01-25
- Evento terminou em: 2024-01-22
- Status: **Anterior** (passou há 3 dias)

Um ingresso é considerado **atual** quando:
- Não tem data de término definida, OU
- A data de término é futura, OU
- A data de término passou há menos de 2 dias

---

## 🎯 Fluxo Completo de Uso

```
1. Login
   POST /api/auth/login
   → Recebe token JWT

2. Listar Ingressos
   GET /api/ingressos
   → Vê todos os ingressos

3. Ver Detalhes + QR Code
   GET /api/ingressos/123
   → Obtém QR Code para validação

4. Refresh Token (opcional)
   POST /api/auth/refresh
   → Renova token sem fazer login novamente
```

---

## 🆘 Tratamento de Erros

```javascript
async function handleIngressos() {
  try {
    const response = await fetch('http://localhost/mundo_app/api/ingressos', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    const data = await response.json();
    
    if (!data.success) {
      // Trata erro retornado pela API
      if (response.status === 401) {
        // Token expirou ou inválido - fazer login novamente
        redirectToLogin();
      } else if (response.status === 404) {
        // Cliente não encontrado
        showError('Perfil não encontrado');
      } else {
        showError(data.message);
      }
    } else {
      // Sucesso
      displayIngressos(data.data.ingressos);
    }
  } catch (error) {
    // Erro de rede ou servidor
    console.error('Erro:', error);
    showError('Erro ao conectar com o servidor');
  }
}
```

---

## 📝 Notas Importantes

1. **QR Code:** Disponível em **todos os endpoints** (index, atuais e show) em formato base64
2. **Formato QR Code:** `data:image/png;base64,...` - pode ser usado diretamente em tags `<img>`
3. **Cartão:** Retorna apenas cartões ativos (não expirados)
4. **Indicações:** Conta apenas pedidos confirmados/pagos
5. **Permissões:** Usuário só acessa seus próprios ingressos (validação automática)

---

## 🔗 Referências

- **Login API:** `API_AUTH_DOCUMENTATION.md`
- **Segurança:** `SECURITY_IMPLEMENTED.md`
- **Exemplos gerais:** `API_AUTH_EXAMPLES.md`

---

**Última Atualização:** 2025-10-25  
**Versão:** 1.0.0  
**Status:** ✅ Pronto para uso

