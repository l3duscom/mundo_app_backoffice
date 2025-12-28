<?php echo $this->extend('Layout/externo'); ?>

<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>

<?php echo $this->section('conteudo') ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow radius-10">
            <div class="card-body p-4 text-center">
                
                <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="bx bx-check" style="font-size: 60px; color: white;"></i>
                </div>
                
                <h2 style="color: #28a745; margin-bottom: 15px;">
                    🎉 Upgrade Realizado com Sucesso! 🎉
                </h2>
                
                <p class="lead mb-4">
                    Parabéns! Seu ingresso foi atualizado.<br>
                    Você receberá um e-mail com os detalhes.
                </p>
                
                <div class="alert alert-success">
                    <i class="bx bx-info-circle me-2"></i>
                    Seu novo ingresso já está disponível na sua área de ingressos!
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo site_url('/console/dashboard/'); ?>" class="btn btn-lg btn-primary shadow">
                        <i class="bx bx-ticket me-2"></i>Ver Meus Ingressos
                    </a>
                </div>
                
                <hr class="my-4">
                
                <p class="text-muted small">
                    O Universo mágico do Dreamfest está te esperando!<br>
                    Você receberá todos os detalhes por e-mail.
                </p>
                
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>
