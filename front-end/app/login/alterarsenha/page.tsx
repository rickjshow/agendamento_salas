"use client";

import { useState } from "react";
import axios from "axios";
import { useRouter } from "next/navigation";

const AlterarSenha: React.FC = () => {
  const [novaSenha, setNovaSenha] = useState<string>("");
  const [confirmarSenha, setConfirmarSenha] = useState<string>("");
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleAlterarSenha = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
  
    if (novaSenha !== confirmarSenha) {
      setError("As senhas não coincidem.");
      return;
    }
  
    try {
      const user = JSON.parse(localStorage.getItem("user") || "{}");
  
      await axios.post(`http://localhost:8000/api/alterar-senha/${user.id}`, {
        senha: novaSenha,
        senha_confirmation: confirmarSenha, // Campo obrigatório para validação
      });
  
      alert("Senha alterada com sucesso!");
      router.push("/login");
    } catch (err: any) {
      setError(err.response?.data?.message || "Erro ao alterar a senha.");
    }
  };
  

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100">
      <div className="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <h1 className="text-2xl font-bold mb-4 text-center">Alterar Senha</h1>
        <form onSubmit={handleAlterarSenha}>
          {error && <div className="text-red-500 text-sm mb-4">{error}</div>}
          <label className="block mb-2">Nova Senha:</label>
          <input
            type="password"
            placeholder="Digite a nova senha"
            className="w-full p-2 mb-4 border rounded"
            value={novaSenha}
            onChange={(e) => setNovaSenha(e.target.value)}
            required
          />
          <label className="block mb-2">Confirmar Nova Senha:</label>
          <input
            type="password"
            placeholder="Confirme a nova senha"
            className="w-full p-2 mb-4 border rounded"
            value={confirmarSenha}
            onChange={(e) => setConfirmarSenha(e.target.value)}
            required
          />
          <button
            type="submit"
            className="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
          >
            Alterar Senha
          </button>
        </form>
      </div>
    </div>
  );
};

export default AlterarSenha;
