"use client";
import React, { useState, useEffect } from "react";
import axios from "axios";
import Layout from "../components/Layout";
import withAuth from "../Hoc/withAuth";

const GerenciamentoAmbientes: React.FC = () => {
  const [ambientes, setAmbientes] = useState([]);
  const [modalAberto, setModalAberto] = useState(false);
  const [ambienteAtual, setAmbienteAtual] = useState<any>(null);
  const [form, setForm] = useState({
    nome: "",
    tipo: "",
    status: "Disponivel",
    descricao: "",
  });

  // Buscar todos os ambientes na API
  useEffect(() => {
    fetchAmbientes();
  }, []);

  const fetchAmbientes = async () => {
    try {
      const response = await axios.get("http://localhost:8000/api/ambientes");
      setAmbientes(response.data);
    } catch (error) {
      console.error("Erro ao buscar ambientes:", error);
    }
  };

  // Abrir modal para criar ou editar ambiente
  const abrirModal = (ambiente: any = null) => {
    setAmbienteAtual(ambiente);
    if (ambiente) {
      setForm(ambiente); // Preencher formulário para edição
    } else {
      setForm({ nome: "", tipo: "", status: "Disponivel", descricao: "" });
    }
    setModalAberto(true);
  };

  // Fechar modal
  const fecharModal = () => {
    setModalAberto(false);
    setAmbienteAtual(null);
  };

  // Atualizar formulário
  const atualizarFormulario = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setForm({ ...form, [name]: value });
  };
  

  // Salvar novo ambiente ou editar existente
  const salvarAmbiente = async () => {
    try {
      if (ambienteAtual) {
        await axios.put(`http://localhost:8000/api/ambientes/${ambienteAtual.id}/edit`, form);
      } else {
        await axios.post("http://localhost:8000/api/ambientes/store", form);
      }
      fetchAmbientes();
      fecharModal();
    } catch (error) {
      console.error("Erro ao salvar ambiente:", error);
    }
  };

  // Excluir ambiente
  const excluirAmbiente = async (id: number) => {
    if (confirm("Tem certeza que deseja excluir este ambiente?")) {
      try {
        await axios.delete(`http://localhost:8000/api/ambientes/${id}`);
        fetchAmbientes();
      } catch (error) {
        console.error("Erro ao excluir ambiente:", error);
      }
    }
  };

  return (
    <Layout>
      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-bold mb-4">Gerenciamento de Ambientes</h1>
        <button
          onClick={() => abrirModal()}
          className="bg-blue-500 text-white px-4 py-2 rounded mb-4"
        >
          Adicionar Ambiente
        </button>
        <table className="table-auto w-full border-collapse border border-gray-300">
          <thead>
            <tr>
              <th className="border border-gray-300 px-4 py-2">Nome</th>
              <th className="border border-gray-300 px-4 py-2">Tipo</th>
              <th className="border border-gray-300 px-4 py-2">Status</th>
              <th className="border border-gray-300 px-4 py-2">Descrição</th>
              <th className="border border-gray-300 px-4 py-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            {ambientes.map((ambiente: any) => (
              <tr key={ambiente.id}>
                <td className="border border-gray-300 px-4 py-2">{ambiente.nome}</td>
                <td className="border border-gray-300 px-4 py-2">{ambiente.tipo}</td>
                <td className="border border-gray-300 px-4 py-2">{ambiente.status}</td>
                <td className="border border-gray-300 px-4 py-2">{ambiente.descricao}</td>
                <td className="border border-gray-300 px-4 py-2">
                  <button
                    onClick={() => abrirModal(ambiente)}
                    className="bg-green-500 text-white px-2 py-1 rounded mr-2"
                  >
                    Editar
                  </button>
                  <button
                    onClick={() => excluirAmbiente(ambiente.id)}
                    className="bg-red-500 text-white px-2 py-1 rounded"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* Modal de Formulário */}
        {modalAberto && (
          <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div className="bg-white p-6 rounded shadow-lg">
              <h2 className="text-xl font-bold mb-4">
                {ambienteAtual ? "Editar Ambiente" : "Adicionar Ambiente"}
              </h2>
              <form>
                <div className="mb-4">
                  <label className="block mb-2">Nome</label>
                  <input
                    type="text"
                    name="nome"
                    value={form.nome}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  />
                </div>
                <div className="mb-4">
                  <label className="block mb-2">Tipo</label>
                  <input
                    type="text"
                    name="tipo"
                    value={form.tipo}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  />
                </div>
                <div className="mb-4">
                        <label className="block mb-2">Status</label>
                        <select
                            name="status"
                            value={form.status}
                            onChange={atualizarFormulario}
                            className="w-full border border-gray-300 px-3 py-2 rounded"
                        >
                            <option value="Disponivel">Disponível</option>
                            <option value="Reservado">Reservado</option>
                            <option value="Manutencao">Manutenção</option>
                        </select>
                        </div>

                <div className="mb-4">
                  <label className="block mb-2">Descrição</label>
                  <textarea
                    name="descricao"
                    value={form.descricao}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  ></textarea>
                </div>
              </form>
              <div className="flex justify-end">
                <button
                  onClick={salvarAmbiente}
                  className="bg-blue-500 text-white px-4 py-2 rounded mr-2"
                >
                  Salvar
                </button>
                <button
                  onClick={fecharModal}
                  className="bg-gray-500 text-white px-4 py-2 rounded"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
};


export default withAuth(GerenciamentoAmbientes, ['admin']);