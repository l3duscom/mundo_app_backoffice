# Exemplos de Uso da API de Autenticação

## Exemplo 1: Login Simples com Postman

### Fazer Login

**Request:**
```
POST http://localhost/mundo_app/api/auth/login
Content-Type: application/json

{
  "email": "admin@exemplo.com",
  "password": "senha123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "nome": "Admin",
      "email": "admin@exemplo.com",
      "is_admin": true
    }
  }
}
```

### Usar o Token para Acessar Perfil

**Request:**
```
GET http://localhost/mundo_app/api/auth/me
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nome": "Admin",
    "email": "admin@exemplo.com",
    "is_admin": true,
    "permissoes": []
  }
}
```

## Exemplo 2: Criar uma API Protegida para Produtos

### Passo 1: Criar o Controller

**Arquivo:** `app/Controllers/Api/Produtos.php`

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Produtos extends BaseController
{
    private $produtoModel;

    public function __construct()
    {
        $this->produtoModel = new \App\Models\ProdutoModel();
    }

    /**
     * Lista todos os produtos
     * GET /api/produtos
     */
    public function index()
    {
        // Acessa dados do usuário autenticado
        $usuarioAutenticado = $this->request->usuarioAutenticado;
        
        // Verifica se tem permissão (exemplo)
        if (!$usuarioAutenticado['is_admin'] && 
            !in_array('listar-produtos', $usuarioAutenticado['permissoes'] ?? [])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Você não tem permissão para listar produtos'
                ])
                ->setStatusCode(403);
        }

        // Busca produtos
        $produtos = $this->produtoModel->findAll();

        return $this->response
            ->setJSON([
                'success' => true,
                'data' => $produtos,
                'user' => [
                    'id' => $usuarioAutenticado['user_id'],
                    'nome' => $usuarioAutenticado['nome']
                ]
            ])
            ->setStatusCode(200);
    }

    /**
     * Cria um novo produto
     * POST /api/produtos
     */
    public function criar()
    {
        $usuarioAutenticado = $this->request->usuarioAutenticado;
        
        // Apenas admin pode criar produtos
        if (!$usuarioAutenticado['is_admin']) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Apenas administradores podem criar produtos'
                ])
                ->setStatusCode(403);
        }

        $dados = $this->request->getJSON(true);

        // Validação
        if (empty($dados['nome'])) {
            return $this->response
                ->setJSON([
                    'success' => false,
                    'message' => 'Nome é obrigatório'
                ])
                ->setStatusCode(400);
        }

        // Cria o produto
        $produtoId = $this->produtoModel->insert($dados);

        return $this->response
            ->setJSON([
                'success' => true,
                'message' => 'Produto criado com sucesso',
                'data' => [
                    'id' => $produtoId
                ]
            ])
            ->setStatusCode(201);
    }
}
```

### Passo 2: Adicionar as Rotas

**Arquivo:** `app/Config/Routes.php`

```php
// Grupo de rotas de produtos protegidas por JWT
$routes->group('api/produtos', ['filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'Api\Produtos::index');
    $routes->post('/', 'Api\Produtos::criar');
    $routes->get('(:num)', 'Api\Produtos::show/$1');
    $routes->put('(:num)', 'Api\Produtos::atualizar/$1');
    $routes->delete('(:num)', 'Api\Produtos::excluir/$1');
});
```

### Passo 3: Testar com cURL

**Listar Produtos:**
```bash
curl -X GET http://localhost/mundo_app/api/produtos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

**Criar Produto:**
```bash
curl -X POST http://localhost/mundo_app/api/produtos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Produto Teste",
    "preco": 99.90,
    "estoque": 100
  }'
```

## Exemplo 3: Frontend com JavaScript

### Classe de API Helper

```javascript
class ApiClient {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
    this.token = localStorage.getItem('token');
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem('token', token);
  }

  clearToken() {
    this.token = null;
    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    
    const config = {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        ...(this.token && { 'Authorization': `Bearer ${this.token}` }),
        ...options.headers
      }
    };

    if (options.body && typeof options.body === 'object') {
      config.body = JSON.stringify(options.body);
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        // Token expirado? Tentar refresh
        if (response.status === 401 && this.token) {
          const refreshed = await this.refreshToken();
          if (refreshed) {
            // Tentar novamente com novo token
            return this.request(endpoint, options);
          }
        }

        throw new Error(data.message || 'Erro na requisição');
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  async login(email, password) {
    const data = await this.request('/api/auth/login', {
      method: 'POST',
      body: { email, password }
    });

    if (data.success) {
      this.setToken(data.data.token);
      localStorage.setItem('refresh_token', data.data.refresh_token);
      return data.data.user;
    }

    throw new Error(data.message);
  }

  async refreshToken() {
    try {
      const refreshToken = localStorage.getItem('refresh_token');
      if (!refreshToken) return false;

      const data = await this.request('/api/auth/refresh', {
        method: 'POST',
        body: { refresh_token: refreshToken }
      });

      if (data.success) {
        this.setToken(data.data.token);
        return true;
      }
    } catch (error) {
      this.clearToken();
    }

    return false;
  }

  async getProfile() {
    return this.request('/api/auth/me');
  }

  logout() {
    this.clearToken();
  }

  // Métodos para produtos (exemplo)
  async getProdutos() {
    return this.request('/api/produtos');
  }

  async criarProduto(produto) {
    return this.request('/api/produtos', {
      method: 'POST',
      body: produto
    });
  }
}

// Uso
const api = new ApiClient('http://localhost/mundo_app');

// Login
async function fazerLogin() {
  try {
    const user = await api.login('admin@exemplo.com', 'senha123');
    console.log('Usuário logado:', user);
    
    // Buscar perfil
    const profile = await api.getProfile();
    console.log('Perfil:', profile);
    
    // Buscar produtos
    const produtos = await api.getProdutos();
    console.log('Produtos:', produtos);
    
  } catch (error) {
    console.error('Erro:', error.message);
  }
}

// Logout
function fazerLogout() {
  api.logout();
  console.log('Usuário deslogado');
}
```

### Exemplo com React

```jsx
import React, { useState, useEffect } from 'react';
import { ApiClient } from './api-client';

const api = new ApiClient('http://localhost/mundo_app');

function App() {
  const [user, setUser] = useState(null);
  const [produtos, setProdutos] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Login
  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const formData = new FormData(e.target);
    const email = formData.get('email');
    const password = formData.get('password');

    try {
      const userData = await api.login(email, password);
      setUser(userData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Carregar produtos
  useEffect(() => {
    if (user) {
      api.getProdutos()
        .then(response => setProdutos(response.data))
        .catch(err => console.error(err));
    }
  }, [user]);

  // Logout
  const handleLogout = () => {
    api.logout();
    setUser(null);
    setProdutos([]);
  };

  if (!user) {
    return (
      <div>
        <h2>Login</h2>
        {error && <div className="error">{error}</div>}
        <form onSubmit={handleLogin}>
          <input 
            type="email" 
            name="email" 
            placeholder="Email" 
            required 
          />
          <input 
            type="password" 
            name="password" 
            placeholder="Senha" 
            required 
          />
          <button type="submit" disabled={loading}>
            {loading ? 'Entrando...' : 'Entrar'}
          </button>
        </form>
      </div>
    );
  }

  return (
    <div>
      <header>
        <h2>Bem-vindo, {user.nome}!</h2>
        <button onClick={handleLogout}>Sair</button>
      </header>

      <main>
        <h3>Produtos</h3>
        <ul>
          {produtos.map(produto => (
            <li key={produto.id}>
              {produto.nome} - R$ {produto.preco}
            </li>
          ))}
        </ul>
      </main>
    </div>
  );
}

export default App;
```

## Exemplo 4: Proteger Rotas com Permissões Específicas

### No Routes.php

```php
// Rota que requer permissão específica
$routes->get('api/relatorios', 'Api\Relatorios::index', [
    'filter' => 'jwtAuth:listar-relatorios'
]);

// Múltiplas rotas com a mesma permissão
$routes->group('api/usuarios', ['filter' => 'jwtAuth:gerenciar-usuarios'], function ($routes) {
    $routes->get('/', 'Api\Usuarios::index');
    $routes->post('/', 'Api\Usuarios::criar');
    $routes->put('(:num)', 'Api\Usuarios::atualizar/$1');
    $routes->delete('(:num)', 'Api\Usuarios::excluir/$1');
});
```

### Verificar Permissão no Controller (alternativa)

```php
public function index()
{
    $usuarioAutenticado = $this->request->usuarioAutenticado;
    
    // Admin sempre pode
    if ($usuarioAutenticado['is_admin']) {
        // Lógica aqui
    }
    
    // Verifica permissão específica
    $permissoes = $usuarioAutenticado['permissoes'] ?? [];
    if (!in_array('listar-relatorios', $permissoes)) {
        return $this->response
            ->setJSON([
                'success' => false,
                'message' => 'Você não tem permissão para acessar relatórios'
            ])
            ->setStatusCode(403);
    }
    
    // Lógica aqui
}
```

## Exemplo 5: App Mobile com Flutter/Dart

```dart
class ApiService {
  final String baseUrl = 'http://localhost/mundo_app';
  String? token;
  String? refreshToken;

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success']) {
      token = data['data']['token'];
      refreshToken = data['data']['refresh_token'];
      
      // Salvar no SharedPreferences
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', token!);
      await prefs.setString('refresh_token', refreshToken!);
      
      return data['data']['user'];
    }

    throw Exception(data['message']);
  }

  Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/auth/me'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success']) {
      return data['data'];
    }

    throw Exception(data['message']);
  }

  Future<void> logout() async {
    token = null;
    refreshToken = null;
    
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('refresh_token');
  }
}
```

## Resumo

Estes exemplos demonstram:

1. ✅ **Login via API** - Receber JWT token
2. ✅ **Autenticação JWT** - Usar token em requisições
3. ✅ **Criar APIs protegidas** - Proteger endpoints com jwtAuth filter
4. ✅ **Verificar permissões** - Controlar acesso por grupos e permissões
5. ✅ **Refresh token** - Renovar token sem fazer login novamente
6. ✅ **Integração frontend** - JavaScript/React/Flutter

Para mais detalhes, consulte `API_AUTH_DOCUMENTATION.md`.

