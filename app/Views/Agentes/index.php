<?php echo $this->extend('Layout/principal'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3 text-muted">
        <a href="<?php echo site_url('/agentes'); ?>">Agentes</a>
    </div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?php echo site_url('/'); ?>"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active">Agentes e Agências</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="<?php echo site_url('agentes/criar'); ?>" class="btn btn-primary">
            <i class="bx bx-plus me-2"></i>Novo Agente
        </a>
    </div>
</div>

<div class="card shadow radius-10">
    <div class="card-body">
        <table id="tabelaAgentes" class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Contato</th>
                    <th>Status</th>
                    <th width="100">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agentes as $agente): ?>
                <tr>
                    <td>
                        <strong><?php echo esc($agente->getNomeExibicao()); ?></strong>
                        <?php if ($agente->nome_fantasia && $agente->nome_fantasia !== $agente->nome): ?>
                        <br><small class="text-muted"><?php echo esc($agente->nome); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-secondary"><?php echo $tipos[$agente->tipo] ?? $agente->tipo; ?></span></td>
                    <td>
                        <?php if ($agente->email): ?>
                        <i class="bx bx-envelope text-muted me-1"></i><?php echo esc($agente->email); ?><br>
                        <?php endif; ?>
                        <?php if ($agente->telefone): ?>
                        <i class="bx bx-phone text-muted me-1"></i><?php echo esc($agente->telefone); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $agente->exibeStatus(); ?></td>
                    <td>
                        <a href="<?php echo site_url("agentes/exibir/{$agente->id}"); ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                            <i class="bx bx-show"></i>
                        </a>
                        <a href="<?php echo site_url("agentes/editar/{$agente->id}"); ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="bx bx-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#tabelaAgentes').DataTable({
        language: { url: '<?php echo site_url('recursos/theme/'); ?>plugins/datatable/pt-BR.json' },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>
<?php echo $this->endSection() ?>
