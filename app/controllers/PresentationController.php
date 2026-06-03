<?php
// FastPlay · Controlador de la presentación interactiva

class PresentationController extends Controller
{
    public function index(): void
    {
        $viewFile = APP_PATH . '/views/presentation/index.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vista de presentación no encontrada.");
        }
        
        // Renderizamos directamente la vista para omitir el layout estándar (main/auth)
        // y poder servir una estructura de diapositivas inmersiva a pantalla completa.
        require $viewFile;
        old_clear();
    }
}
