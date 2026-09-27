<?php
require_once _DIR_ . '/Livro.php';

/**
 * DESAFIO (Aula 3):
 * Este módulo une Aluno + Livro. As consultas de listagem já estão prontas
 * (usam JOIN para trazer o nome do aluno e o título do livro). O desafio é
 * implementar as duas regras de negócio principais: realizar() e devolver().
 */
class Emprestimo {
    public $id;
    public $aluno_id;
    public $livro_id;
    public $funcionario_id;
    public $data_emprestimo;
    public $data_devolucao_prevista;
    public $data_devolucao_real;
    public $status;

    public $conexao;

    public function __construct($conexao) {
        $this->conexao = $conexao;
    }

    // Já pronto: lista todos os empréstimos com nome do aluno e título do livro.
    public function listarTodos() {
        $query = "SELECT e.*, a.nome AS aluno_nome, l.titulo AS livro_titulo
                   FROM emprestimo e
                   JOIN aluno a ON a.id = e.aluno_id
                   JOIN livro l ON l.id = e.livro_id
                   ORDER BY e.data_emprestimo DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Já pronto: lista os empréstimos de um aluno específico.
    public function listarPorAluno($aluno_id) {
        $query = "SELECT e.*, l.titulo AS livro_titulo
                   FROM emprestimo e
                   JOIN livro l ON l.id = e.livro_id
                   WHERE e.aluno_id = :aluno_id
                   ORDER BY e.data_emprestimo DESC";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(':aluno_id', $aluno_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $query = "SELECT * FROM emprestimo WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar um novo empréstimo.
     */
    public function realizar() {
        $livroModel = new Livro($this->conexao);
        $livro = $livroModel->buscarPorId($this->livro_id);

        if (!$livro || $livro['quantidade_disponivel'] <= 0) {
            return false;
        }

        $query = "INSERT INTO emprestimo (aluno_id, livro_id, funcionario_id, status, data_emprestimo, data_devolucao_prevista) 
                  VALUES (:aluno_id, :livro_id, :funcionario_id, 'em_andamento', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))";
        
        $stmt = $this->conexao->prepare($query);
        $sucesso = $stmt->execute([
            ':aluno_id' => $this->aluno_id,
            ':livro_id' => $this->livro_id,
            ':funcionario_id' => $this->funcionario_id
        ]);

        if ($sucesso) {
            $livroModel->atualizarDisponibilidade($this->livro_id, -1);
            return true;
        }

        return false;
    }

    /**
     * Registrar a devolução do empréstimo com o $id informado.
     */
    public function devolver($id) {
        $emprestimo = $this->buscarPorId($id);

        if (!$emprestimo) {
            return false;
        }

        $query = "UPDATE emprestimo 
                  SET data_devolucao_real = CURDATE(), status = 'devolvido' 
                  WHERE id = :id";
        
        $stmt = $this->conexao->prepare($query);
        $sucesso = $stmt->execute([':id' => $id]);

        if ($sucesso) {
            $livroModel = new Livro($this->conexao);
            $livroModel->atualizarDisponibilidade($emprestimo['livro_id'], 1);
            return true;
        }

        return false;
    }
}