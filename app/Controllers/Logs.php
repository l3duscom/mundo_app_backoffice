<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Logs extends BaseController
{
	private $logPath;

	public function __construct()
	{
		$this->logPath = WRITEPATH . 'logs/';
	}

	/**
	 * Lista os arquivos de log com filtros
	 */
	public function index()
	{
		if (!$this->usuarioLogado() || !$this->usuarioLogado()->is_admin) {
			return redirect()->back()->with('atencao', 'Acesso restrito a administradores.');
		}

		helper('filesystem');

		$files = get_filenames($this->logPath, true);

		$fileInfo = [];
		foreach ($files as $file) {
			if (!str_ends_with($file, '.log')) {
				continue;
			}
			$filemtime = filemtime($file);
			$fileInfo[] = [
				'caminho'  => $file,
				'nome'     => basename($file),
				'data'     => str_replace(['log-', '.log'], '', basename($file)),
				'tamanho'  => $this->formatBytes(filesize($file)),
				'modified' => date('d/m/Y H:i', $filemtime),
				'timestamp' => $filemtime,
			];
		}

		// Ordena do mais recente para o mais antigo
		usort($fileInfo, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

		$data = [
			'titulo'   => 'Logs do Sistema',
			'arquivos' => $fileInfo,
		];

		return view('Logs/index', $data);
	}

	/**
	 * Visualiza um arquivo de log com filtros
	 */
	public function view($file)
	{
		if (!$this->usuarioLogado() || !$this->usuarioLogado()->is_admin) {
			return redirect()->back()->with('atencao', 'Acesso restrito a administradores.');
		}

		$file = basename($file); // Segurança: evita path traversal

		if (!is_file($this->logPath . $file)) {
			return redirect()->to('/logs')->with('atencao', 'Arquivo de log não encontrado.');
		}

		$filtro = $this->request->getGet('filtro') ?? '';
		$nivel = $this->request->getGet('nivel') ?? '';

		$conteudo = file($this->logPath . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$linhas = [];
		foreach ($conteudo as $linha) {
			$parsed = $this->parseLinha($linha);
			if ($parsed === null) {
				continue;
			}

			// Filtro por nível
			if ($nivel && strtoupper($parsed['nivel']) !== strtoupper($nivel)) {
				continue;
			}

			// Filtro por texto
			if ($filtro && stripos($parsed['mensagem'], $filtro) === false) {
				continue;
			}

			$linhas[] = $parsed;
		}

		// Mais recentes primeiro
		$linhas = array_reverse($linhas);

		// Contadores por nível
		$contadores = ['INFO' => 0, 'ERROR' => 0, 'WARNING' => 0, 'DEBUG' => 0, 'CRITICAL' => 0];
		foreach ($conteudo as $linha) {
			$parsed = $this->parseLinha($linha);
			if ($parsed !== null && isset($contadores[$parsed['nivel']])) {
				$contadores[$parsed['nivel']]++;
			}
		}

		$data = [
			'titulo'     => 'Log: ' . $file,
			'filename'   => $file,
			'linhas'     => $linhas,
			'filtro'     => $filtro,
			'nivel'      => $nivel,
			'contadores' => $contadores,
			'total'      => count($linhas),
		];

		return view('Logs/view', $data);
	}

	/**
	 * Faz parse de uma linha de log do CodeIgniter 4
	 */
	private function parseLinha(string $linha): ?array
	{
		if (preg_match('/^(INFO|ERROR|DEBUG|WARNING|NOTICE|CRITICAL|ALERT|EMERGENCY)\s+-\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+-->\s+(.*)$/s', $linha, $matches)) {
			return [
				'nivel'    => $matches[1],
				'data'     => $matches[2],
				'mensagem' => $matches[3],
			];
		}

		return null;
	}

	/**
	 * Formata bytes para exibição legível
	 */
	private function formatBytes(int $bytes): string
	{
		if ($bytes >= 1048576) {
			return number_format($bytes / 1048576, 1) . ' MB';
		}
		if ($bytes >= 1024) {
			return number_format($bytes / 1024, 1) . ' KB';
		}
		return $bytes . ' B';
	}
}
