<?php

/**
 * ATIVIDADE EM SALA (Aula 2):
 * Complete os métodos abaixo seguindo exatamente o mesmo padrão usado em
 * models/Livro.php (que já está pronto e funcionando). A tabela aluno
 * já existe no banco (veja database/schema.sql) com as colunas:
 * id, nome, matricula, email.
 */
class Aluno {
    public $id;
    public $nome;
    public $matricula;
    public $email;

    public $conexao;

    public function __construct($conexao) {
        $this->conexao =$conexao;
    }

    // Insere um novo aluno na tabela aluno
    public function create() {
        $query = "INSERT INTO aluno (nome, matricula, email) VALUES (:nome, :matricula, :email)";
        $stmt = $this->conexao->prepare($query);
        return $stmt->execute([
            ':nome' => $this->nome,
            ':matricula' => $this->matricula,
            ':email' => $this->email
        ]);
    }

    // Retorna todos os alunos cadastrados, ordenados por nome
    public function listarTodos() {
        $query = "SELECT * FROM aluno ORDER BY nome";
        $stmt =$this->conexao->prepare($query);$stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retorna os dados de um único aluno a partir do $id
    public function buscarPorId($id) {$query = "SELECT * FROM aluno WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        $stmt->execute([':id' =>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Atualiza nome, matricula e email do aluno com o $this->id informado
    public function atualizar() {
        $query = "UPDATE aluno SET nome = :nome, matricula = :matricula, email = :email WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        return $stmt->execute([
            ':id' => $this->id,
            ':nome' => $this->nome,
            ':matricula' => $this->matricula,
            ':email' => $this->email
        ]);
    }

    // Remova o aluno com o $id informado
    public function excluir($id) {$query = "DELETE FROM aluno WHERE id = :id";
        $stmt = $this->conexao->prepare($query);
        return $stmt->execute([':id' =>$id]);
    }
}