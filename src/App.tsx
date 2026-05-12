import React, { useState, useRef } from 'react';
import { Info, Edit3, Save, Plus, Trash2, Camera, Hash } from 'lucide-react';

interface TagInfo {
  id: number;
  tag: string;
  description: string;
  useCase: string;
}

const HTML_EXPLANATIONS: TagInfo[] = [
  {
    id: 1,
    tag: "<h1>",
    description: "Elemen heading tingkat pertama. Digunakan untuk judul utama halaman.",
    useCase: "Hanya boleh ada satu <h1> per halaman untuk SEO yang baik."
  },
  {
    id: 2,
    tag: "<p>",
    description: "Elemen paragraf. Digunakan untuk blok teks naratif.",
    useCase: "Mengelompokkan kalimat-kalimat menjadi satu kesatuan teks."
  },
  {
    id: 3,
    tag: "<a>",
    description: "Elemen anchor (tautan). Digunakan untuk menghubungkan ke halaman atau file lain.",
    useCase: "Menggunakan atribut 'href' untuk menentukan tujuan tautan."
  },
  {
    id: 4,
    tag: "<h2>",
    description: "Elemen heading tingkat kedua. Digunakan untuk sub-judul bagian.",
    useCase: "Membagi konten menjadi bagian-bagian besar seperti 'Pengalaman Kerja' atau 'Pendidikan'."
  },
  {
    id: 5,
    tag: "<ul> & <li>",
    description: "Unordered List (Daftar tidak berurutan) dan List Item.",
    useCase: "Digunakan untuk poin-poin yang tidak memiliki urutan prioritas tertentu."
  },
  {
    id: 6,
    tag: "<strong>",
    description: "Elemen penekanan kuat. Biasanya merender teks menjadi tebal.",
    useCase: "Menandai kata penting dalam sebuah kalimat."
  },
  {
    id: 7,
    tag: "<em>",
    description: "Elemen penekanan (emphasis). Biasanya merender teks menjadi miring.",
    useCase: "Digunakan untuk memberikan tekanan pada kata tertentu secara linguistik."
  },
  {
    id: 8,
    tag: "<table>, <tr>, <td>",
    description: "Elemen tabel, baris tabel, dan data tabel.",
    useCase: "Menampilkan data tabular yang memiliki hubungan baris dan kolom."
  },
  {
    id: 9,
    tag: "<footer>",
    description: "Elemen footer. Digunakan untuk informasi di bagian bawah halaman atau section.",
    useCase: "Berisi informasi hak cipta, kontak, atau tautan tambahan."
  },
  {
    id: 10,
    tag: "<img>",
    description: "Elemen gambar. Digunakan untuk menyisipkan konten visual ke dalam dokumen.",
    useCase: "Menggunakan atribut 'src' untuk sumber gambar dan 'alt' untuk teks alternatif bagi aksesibilitas."
  }
];

export default function App() {
  const [showExplanations, setShowExplanations] = useState(true);
  const [isEditing, setIsEditing] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [cvData, setCvData] = useState(() => {
    const saved = localStorage.getItem('cvData');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {
        console.error("Failed to parse saved CV data", e);
      }
    }
    return {
      name: "Erga Refaldy Dwi Gustian",
      role: "IT Student",
      email: "erga@gmail.com",
      location: "Bandung, ID",
      phone: "+62 812 3456 789",
      image: "https://picsum.photos/seed/profile/200/200",
      experience: [
        {
          id: 1,
          title: "Senior Web Developer - Tech Solutions Inc.(Placeholder)",
          period: "Jan 2020 — PRES",
          tasks: [
            "Memimpin tim frontend dalam pembuatan Sistem ERP",
            "Optimasi DOM tree dan performa situs hingga 40% lebih cepat"
          ]
        }
      ],
      education: [
        {
          id: 1,
          period: "2023 - 2027",
          institution: "Universitas Bale Bandung",
          degree: "-"
        }
      ]
    };
  });

  // Persist to localStorage whenever cvData changes
  React.useEffect(() => {
    localStorage.setItem('cvData', JSON.stringify(cvData));
  }, [cvData]);

  const Tag = ({ id }: { id: number }) => (
    <sup className="inline-flex items-center justify-center bg-red-600 text-white w-4 h-4 rounded-full text-[9px] font-bold ml-1 cursor-help group relative shadow-sm shrink-0" title={`Lihat penjelasan #${id}`}>
      {id}
    </sup>
  );

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setCvData({ ...cvData, image: reader.result as string });
      };
      reader.readAsDataURL(file);
    }
  };

  const updateExperience = (id: number, field: string, value: any) => {
    const newExperience = cvData.experience.map(exp => 
      exp.id === id ? { ...exp, [field]: value } : exp
    );
    setCvData({ ...cvData, experience: newExperience });
  };

  const addExperience = () => {
    const newExp = {
      id: Date.now(),
      title: "New Job Title - Company",
      period: "Period",
      tasks: ["Describe your responsibilities..."]
    };
    setCvData({ ...cvData, experience: [...cvData.experience, newExp] });
  };

  const removeExperience = (id: number) => {
    setCvData({ ...cvData, experience: cvData.experience.filter(e => e.id !== id) });
  };

  const updateEducation = (id: number, field: string, value: string) => {
    const newEducation = cvData.education.map(edu => 
      edu.id === id ? { ...edu, [field]: value } : edu
    );
    setCvData({ ...cvData, education: newEducation });
  };

  const addEducation = () => {
    const newEdu = {
      id: Date.now(),
      period: "Year Range",
      institution: "Institution Name",
      degree: "Degree / Certification"
    };
    setCvData({ ...cvData, education: [...cvData.education, newEdu] });
  };

  const removeEducation = (id: number) => {
    setCvData({ ...cvData, education: cvData.education.filter(e => e.id !== id) });
  };

  return (
    <div className="min-h-screen bg-[#F5F5F5] text-[#1A1A1A] font-sans p-4 md:p-10 flex flex-col">
      {/* HEADER */}
      <header className="max-w-7xl mx-auto w-full mb-6 md:mb-8 border-b border-black pb-4 flex flex-col md:flex-row justify-between items-start md:items-end gap-3">
        <div className="flex flex-wrap items-start">
          <div className="text-left">
            <h1 className="text-2xl md:text-4xl font-bold tracking-tight uppercase leading-none">Curriculum Vitae</h1>
            <p className="text-[10px] md:text-sm text-gray-600 mt-1 uppercase tracking-wider font-semibold">Standard Technical Document v1.0.42</p>
          </div>
          <span className="ml-0 md:ml-4 mt-2 md:mt-0 bg-black text-white px-2 py-0.5 md:py-1 text-[9px] md:text-[10px] rounded uppercase font-bold tracking-wider">
            REF: 2026/HTML-STRUCT
          </span>
        </div>
        <div className="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-2">
          <div className="text-[9px] md:text-[10px] uppercase font-bold tracking-[0.1em] md:tracking-[0.2em] text-gray-400">Technical View</div>
          <div className="flex gap-2">
            <button 
              onClick={() => setIsEditing(!isEditing)}
              className={`px-2 md:px-3 py-1 border border-black text-[9px] md:text-[10px] uppercase font-bold flex items-center gap-1.5 shadow-sm active:scale-95 transition-all ${isEditing ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-50'}`}
            >
              {isEditing ? <Save className="w-3 h-3" /> : <Edit3 className="w-3 h-3" />}
              {isEditing ? "Save Changes" : "Edit Document"}
            </button>
            <button 
              onClick={() => setShowExplanations(!showExplanations)}
              className="px-2 md:px-3 py-1 bg-white border border-black text-[9px] md:text-[10px] uppercase font-bold hover:bg-black hover:text-white transition-all flex items-center gap-1.5 shadow-sm active:scale-95"
            >
              <Info className="w-3 h-3" />
              {showExplanations ? "Hide Metadata" : "Show Metadata"}
            </button>
          </div>
        </div>
      </header>

      {/* MAIN CONTENT AREA */}
      <main className={`max-w-7xl mx-auto w-full flex-1 grid ${showExplanations ? "lg:grid-cols-2" : "grid-cols-1"} gap-6 md:gap-10`}>
        
        {/* CV SECTION */}
        <div className="bg-white border border-gray-300 p-5 md:p-12 shadow-sm flex flex-col h-full ring-1 ring-black/5 overflow-hidden">
          <article className="flex-1">
            <header className="mb-6 flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-gray-100 pb-6 text-left">
              <div className="flex gap-4 md:gap-6 w-full">
                <div className="relative shrink-0">
                  <input 
                    type="file" 
                    className="hidden" 
                    ref={fileInputRef} 
                    onChange={handleImageChange} 
                    accept="image/*"
                  />
                  <img 
                    src={cvData.image} 
                    alt="Foto Profil" 
                    className="w-16 h-16 md:w-24 md:h-24 object-cover border border-black grayscale transition-all hover:grayscale-0 shadow-sm"
                    referrerPolicy="no-referrer"
                  />
                  {isEditing && (
                    <button 
                      onClick={() => fileInputRef.current?.click()}
                      className="absolute inset-0 bg-black/60 flex items-center justify-center text-white opacity-0 hover:opacity-100 transition-opacity"
                    >
                      <Camera className="w-6 h-6" />
                    </button>
                  )}
                  <div className="absolute -bottom-1.5 -right-1.5">
                    <Tag id={10} />
                  </div>
                </div>
                <div className="flex-1">
                  {isEditing ? (
                    <input 
                      className="text-xl md:text-2xl font-bold uppercase tracking-tight w-full bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                      value={cvData.name}
                      onChange={(e) => setCvData({ ...cvData, name: e.target.value })}
                    />
                  ) : (
                    <h1 className="text-xl md:text-2xl font-bold uppercase tracking-tight flex items-center flex-wrap">
                      {cvData.name} <Tag id={1} />
                    </h1>
                  )}
                  
                  {isEditing ? (
                    <input 
                      className="text-xs md:text-sm font-medium text-gray-700 mt-1 w-full bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                      value={cvData.role}
                      onChange={(e) => setCvData({ ...cvData, role: e.target.value })}
                    />
                  ) : (
                    <p className="text-xs md:text-sm font-medium text-gray-700 mt-1">
                      {cvData.role} <Tag id={2} />
                    </p>
                  )}

                  <div className="text-[10px] md:text-xs text-gray-500 mt-2 flex flex-col gap-1">
                    <span className="flex items-center gap-1">
                      {isEditing ? (
                        <input 
                          className="text-black font-bold w-full bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                          value={cvData.email}
                          onChange={(e) => setCvData({ ...cvData, email: e.target.value })}
                        />
                      ) : (
                        <a href={`mailto:${cvData.email}`} className="text-black font-bold hover:underline">{cvData.email}</a>
                      )}
                      <Tag id={3} />
                    </span>
                    {isEditing ? (
                      <div className="flex flex-col gap-1 mt-1">
                        <input 
                          className="bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                          value={cvData.location}
                          onChange={(e) => setCvData({ ...cvData, location: e.target.value })}
                          placeholder="Location"
                        />
                        <input 
                          className="bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                          value={cvData.phone}
                          onChange={(e) => setCvData({ ...cvData, phone: e.target.value })}
                          placeholder="Phone"
                        />
                      </div>
                    ) : (
                      <span className="text-gray-400">{cvData.location} • {cvData.phone}</span>
                    )}
                  </div>
                </div>
              </div>
              {!isEditing && (
                <div className="hidden sm:block bg-gray-50 border border-gray-200 px-2 py-1 text-[9px] font-mono text-gray-400 shrink-0">
                  &lt;header&gt;_ID_001
                </div>
              )}
            </header>

            <section className="mb-8 border-t border-gray-200 pt-6">
              <div className="flex justify-between items-center mb-5">
                <h2 className="text-[10px] md:text-xs font-black uppercase tracking-[0.2em] flex items-center text-gray-400 text-left">
                  Pengalaman Kerja <Tag id={4} />
                </h2>
                {isEditing && (
                  <button onClick={addExperience} className="p-1 text-black hover:bg-gray-100 rounded">
                    <Plus className="w-4 h-4" />
                  </button>
                )}
              </div>
              
              <div className="space-y-6 md:space-y-8">
                {cvData.experience.map((exp) => (
                  <div key={exp.id} className="relative group text-left">
                    {isEditing && (
                      <button 
                        onClick={() => removeExperience(exp.id)}
                        className="absolute -left-8 top-0 p-1 text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    )}
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-baseline mb-1 gap-1">
                      {isEditing ? (
                        <input 
                          className="text-xs md:text-sm font-bold uppercase leading-snug bg-yellow-50 border-b border-yellow-200 outline-none p-1 w-full sm:w-auto flex-1 mr-2"
                          value={exp.title}
                          onChange={(e) => updateExperience(exp.id, 'title', e.target.value)}
                        />
                      ) : (
                        <h3 className="text-xs md:text-sm font-bold uppercase leading-snug">{exp.title}</h3>
                      )}
                      
                      {isEditing ? (
                        <input 
                          className="text-[9px] md:text-[10px] font-mono text-gray-400 shrink-0 uppercase tracking-tighter bg-yellow-50 border-b border-yellow-200 outline-none p-1"
                          value={exp.period}
                          onChange={(e) => updateExperience(exp.id, 'period', e.target.value)}
                        />
                      ) : (
                        <span className="text-[9px] md:text-[10px] font-mono text-gray-400 shrink-0 uppercase tracking-tighter">{exp.period}</span>
                      )}
                    </div>
                    <ul className="text-xs md:text-sm space-y-2 mt-3 list-inside">
                      {exp.tasks.map((task, tidx) => (
                        <li key={tidx} className="flex items-start gap-2 group/task">
                          <span className="text-gray-300 mt-0.5 shrink-0">•</span>
                          {isEditing ? (
                            <div className="flex-1 flex gap-2">
                              <textarea 
                                className="leading-relaxed text-gray-700 bg-yellow-50 border-b border-yellow-200 outline-none p-1 w-full"
                                value={task}
                                onChange={(e) => {
                                  const newTasks = [...exp.tasks];
                                  newTasks[tidx] = e.target.value;
                                  updateExperience(exp.id, 'tasks', newTasks);
                                }}
                                rows={1}
                              />
                              <button 
                                onClick={() => {
                                  const newTasks = exp.tasks.filter((_, i) => i !== tidx);
                                  updateExperience(exp.id, 'tasks', newTasks);
                                }}
                                className="text-red-300 hover:text-red-500"
                              >
                                <Trash2 className="w-3 h-3" />
                              </button>
                            </div>
                          ) : (
                            <span className="leading-relaxed text-gray-700">
                              {task.includes("Sistem ERP") ? (
                                <>Memimpin tim frontend dalam pembuatan <strong>Sistem ERP</strong> <Tag id={6} /> <Tag id={5} /></>
                              ) : task.includes("40% lebih cepat") ? (
                                <>Optimasi DOM tree dan performa situs hingga <em>40% lebih cepat</em> <Tag id={7} /></>
                              ) : task}
                            </span>
                          )}
                        </li>
                      ))}
                      {isEditing && (
                        <button 
                          onClick={() => {
                            updateExperience(exp.id, 'tasks', [...exp.tasks, "New task detail..."]);
                          }}
                          className="text-[10px] text-gray-400 flex items-center gap-1 mt-1 hover:text-black"
                        >
                          <Plus className="w-3 h-3" /> Add Detail
                        </button>
                      )}
                    </ul>
                  </div>
                ))}
              </div>
            </section>

            <section className="mb-6 border-t border-gray-200 pt-6">
              <div className="flex justify-between items-center mb-5">
                <h2 className="text-[10px] md:text-xs font-black uppercase tracking-[0.2em] flex items-center text-gray-400 text-left">
                  Pendidikan <Tag id={4} />
                </h2>
                {isEditing && (
                  <button onClick={addEducation} className="p-1 text-black hover:bg-gray-100 rounded">
                    <Plus className="w-4 h-4" />
                  </button>
                )}
              </div>
              <div className="overflow-x-auto -mx-1 px-1">
                <table className="w-full text-left border-collapse text-xs md:text-sm min-w-[320px]">
                  <thead>
                    <tr className="border-b border-gray-200 font-mono text-[9px] md:text-[10px] uppercase text-gray-400">
                      <th className="py-2 pr-4">Period</th>
                      <th className="py-2 pr-4">Institution</th>
                      <th className="py-2">Degree</th>
                      {isEditing && <th className="py-2 w-8"></th>}
                    </tr>
                  </thead>
                  <tbody className="font-medium">
                    {cvData.education.map((edu) => (
                      <tr key={edu.id} className="border-b border-gray-50 group">
                        <td className="py-3 pr-4 whitespace-nowrap">
                          {isEditing ? (
                            <input 
                              className="bg-yellow-50 p-1 outline-none w-full"
                              value={edu.period}
                              onChange={(e) => updateEducation(edu.id, 'period', e.target.value)}
                            />
                          ) : edu.period}
                        </td>
                        <td className="py-3 pr-4">
                          {isEditing ? (
                            <input 
                              className="bg-yellow-50 p-1 outline-none w-full"
                              value={edu.institution}
                              onChange={(e) => updateEducation(edu.id, 'institution', e.target.value)}
                            />
                          ) : edu.institution}
                        </td>
                        <td className="py-3 italic">
                          {isEditing ? (
                            <input 
                              className="bg-yellow-50 p-1 outline-none w-full"
                              value={edu.degree}
                              onChange={(e) => updateEducation(edu.id, 'degree', e.target.value)}
                            />
                          ) : edu.degree}
                        </td>
                        {isEditing && (
                          <td className="py-3">
                            <button onClick={() => removeEducation(edu.id)} className="text-red-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </td>
                        )}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              <div className="mt-4 p-2 bg-gray-50 border border-gray-100 flex items-center gap-2">
                <Hash className="w-3 h-3 text-gray-300 shrink-0" />
                <span className="text-[9px] md:text-[10px] text-gray-400 font-mono leading-tight">Data context: &lt;table&gt; element <Tag id={8} /></span>
              </div>
            </section>

            <footer className="mt-10 pt-6 border-t border-black/10 flex justify-between items-center text-left">
              <p className="text-[9px] md:text-[10px] font-bold uppercase tracking-widest text-gray-400">
                &copy; 2026 {cvData.name} <Tag id={9} />
              </p>
              <div className="flex gap-2">
                <div className="w-1.5 h-1.5 bg-black"></div>
                <div className="w-1.5 h-1.5 bg-black/20"></div>
              </div>
            </footer>
          </article>
        </div>

        {/* EXPLANATION SECTION (High Density Dark Theme) */}
        {showExplanations && (
          <aside className="bg-[#1E1E1E] text-[#D4D4D4] font-mono border-l-4 border-yellow-500 flex flex-col h-full shadow-lg overflow-hidden text-left">
            <div className="p-5 md:p-6 flex flex-col h-full">
              <h2 className="text-yellow-500 text-[10px] md:text-xs font-bold uppercase tracking-[0.2em] mb-5 flex items-center gap-2">
                <Info className="w-3.5 h-3.5" />
                Table: HTML Explanations
              </h2>
              
              <div className="flex-1 overflow-y-auto space-y-1 pr-1 custom-scrollbar-dark max-h-[400px] lg:max-h-full">
                {HTML_EXPLANATIONS.map((item) => (
                  <div key={item.id} className="border-b border-white/5 pb-3 mb-2 hover:bg-white/5 transition-colors p-2 rounded group">
                    <div className="flex items-start gap-2 mb-1.5">
                      <span className="text-red-500 font-bold shrink-0 text-[10px] md:text-[11px]">[{item.id}]</span>
                      <span className="text-blue-400 font-bold text-[10px] md:text-[11px] truncate">{item.tag}</span>
                    </div>
                    <p className="text-[10px] md:text-[11px] leading-relaxed text-gray-400 pl-6 group-hover:text-gray-200 transition-colors">
                      {item.description}
                    </p>
                  </div>
                ))}
              </div>

              <div className="mt-5 p-3 bg-black/40 border border-white/5 rounded text-[9px] text-gray-500 leading-normal italic">
                <span className="text-yellow-500 font-bold uppercase block mb-0.5">Note:</span>
                Proper tagging improves SEO & accessible. Click markers to sync.
              </div>
            </div>
          </aside>
        )}
      </main>

      {/* FOOTER */}
      <footer className="max-w-7xl mx-auto w-full mt-8 flex flex-col md:flex-row justify-between items-center text-[10px] text-gray-500 uppercase tracking-[0.3em] font-bold border-t border-gray-300 pt-4">
        <div>Generated System Preview — Build 042.SBN</div>
        <div className="flex gap-8 mt-2 md:mt-0">
          <span>Viewport: Optimized</span>
          <span>Status: Verified_Structural</span>
        </div>
      </footer>

      <style>{`
        .custom-scrollbar-dark::-webkit-scrollbar {
          width: 4px;
        }
        .custom-scrollbar-dark::-webkit-scrollbar-track {
          background: rgba(255,255,255,0.05);
        }
        .custom-scrollbar-dark::-webkit-scrollbar-thumb {
          background: rgba(255,255,255,0.1);
          border-radius: 10px;
        }
        .custom-scrollbar-dark::-webkit-scrollbar-thumb:hover {
          background: rgba(255,255,255,0.2);
        }
      `}</style>
    </div>
  );
}
