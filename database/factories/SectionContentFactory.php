<?php

namespace Database\Factories;

use App\Models\SectionContent;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SectionContent>
 */
class SectionContentFactory extends Factory
{
    protected $model = SectionContent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contentTypes = [
            'Video Tutorial',
            'Reading Material',
            'Code Exercise',
            'Quiz',
            'Assignment',
            'Live Demo',
            'Case Study',
            'Downloadable Resource'
        ];

        // Generate rich text content with TipTap format
        $richContent = $this->generateRichTextContent();
        
        return [
            'name' => $this->faker->randomElement($contentTypes) . ': ' . $this->faker->sentence(3),
            'course_section_id' => CourseSection::factory(),
            'content' => $richContent,
            'is_free' => $this->faker->boolean(30), // 30% chance of being free
        ];
    }
    
    /**
     * Generate rich text content with various HTML elements
     */
    private function generateRichTextContent(): string
    {
        $contentVariations = [
            // Content with bullet lists and nested lists
            '<h1>Pengenalan Konsep Dasar</h1><p>Dalam pelajaran ini, kita akan membahas berbagai konsep fundamental yang penting untuk dipahami.</p><h2>Materi yang Akan Dipelajari</h2><ul><li>Konsep dasar dan terminologi</li><li>Implementasi praktis dalam proyek nyata</li><li>Best practices yang direkomendasikan<ul><li>Perencanaan yang matang</li><li>Dokumentasi yang lengkap</li><li>Testing yang komprehensif</li></ul></li><li>Tips dan trik untuk optimisasi</li></ul><blockquote><p>"Belajar adalah investasi terbaik yang bisa Anda lakukan untuk masa depan. Setiap pengetahuan baru membuka pintu kesempatan yang tak terbatas."</p></blockquote><p>Mari kita mulai dengan memahami dasar-dasarnya.</p>',
            
            // Content with ordered lists and code blocks
            '<h2>Langkah-langkah Implementation</h2><p>Berikut adalah panduan sistematis untuk mengimplementasikan konsep yang telah kita pelajari:</p><ol><li>Persiapan environment dan tools</li><li>Setup konfigurasi awal<ol><li>Database configuration</li><li>API endpoint setup</li><li>Authentication middleware</li></ol></li><li>Development phase</li><li>Testing dan debugging</li><li>Deployment ke production</li></ol><p>Contoh kode untuk implementasi:</p><pre><code>function initializeApp() {\n    const config = {\n        apiUrl: "https://api.example.com",\n        timeout: 5000\n    };\n    \n    return new AppService(config);\n}</code></pre><p><strong>Penting:</strong> Pastikan semua dependensi sudah terinstall sebelum menjalankan kode di atas.</p>',
            
            // Content with task lists (showcasing new TipTap feature)
            '<div style="text-align: center;"><h1>Checklist Pembelajaran</h1></div><p>Untuk memastikan pemahaman yang optimal, silakan ikuti checklist berikut:</p><h3>Persiapan Belajar</h3><ul data-type="taskList"><li><input type="checkbox" checked> Siapkan lingkungan belajar yang kondusif</li><li><input type="checkbox"> Baca materi pendahuluan</li><li><input type="checkbox"> Download semua resources yang diperlukan</li></ul><h4>Proses Pembelajaran</h4><ul data-type="taskList"><li><input type="checkbox"> Tonton video pembelajaran</li><li><input type="checkbox"> Praktikkan setiap contoh kode</li><li><input type="checkbox"> Kerjakan latihan soal</li><li><input type="checkbox"> Diskusi di forum komunitas</li></ul><blockquote><p>Konsistensi dalam belajar lebih penting daripada intensitas. Luangkan waktu setiap hari untuk mengembangkan kemampuan Anda.</p></blockquote><h5>Tips Tambahan</h5><p>Gunakan <mark>highlighting</mark> untuk menandai bagian penting dan jangan lupa untuk <u>mencatat</u> hal-hal yang perlu diingat.</p>',
            
            // Content with mixed formatting and alignment
            '<div style="text-align: center;"><h1>Studi Kasus Lengkap</h1></div><p>Mari kita analisis sebuah kasus nyata untuk memahami penerapan konsep-konsep yang telah dipelajari.</p><h3>Analisis Requirement</h3><ul><li><strong>Functional Requirements</strong><ul><li>User authentication system</li><li>Data management interface</li><li>Real-time notifications</li></ul></li><li><strong>Non-functional Requirements</strong><ul><li>Performance: Response time &lt; 2 seconds</li><li>Security: SSL encryption mandatory</li><li>Scalability: Support 10,000+ concurrent users</li></ul></li></ul><blockquote><p>Dalam pengembangan software, detail kecil sering kali membuat perbedaan besar. Perhatikan setiap aspek dengan seksama.</p></blockquote><div style="text-align: right;"><p><em>"Excellence is not a skill, it\'s an attitude." - Ralph Marston</em></p></div>',
            
            // Content with tables and advanced formatting  
            '<h2>Perbandingan Teknologi</h2><p>Berikut adalah perbandingan berbagai teknologi yang dapat digunakan dalam proyek ini:</p><table><thead><tr><th>Teknologi</th><th>Kelebihan</th><th>Kekurangan</th><th>Use Case</th></tr></thead><tbody><tr><td>React</td><td>Component-based, Large ecosystem</td><td>Learning curve</td><td>Web Applications</td></tr><tr><td>Vue.js</td><td>Easy to learn, Flexible</td><td>Smaller community</td><td>Progressive Enhancement</td></tr><tr><td>Angular</td><td>Full framework, TypeScript</td><td>Complex setup</td><td>Enterprise Applications</td></tr></tbody></table><h3>Rekomendasi Pilihan</h3><ol><li><strong>Untuk pemula:</strong> Vue.js karena kurva pembelajaran yang mudah</li><li><strong>Untuk proyek enterprise:</strong> Angular dengan ekosistem yang lengkap</li><li><strong>Untuk flexibility:</strong> React dengan community yang besar</li></ol><hr><p style="text-align: center;"><em>Pilihan teknologi harus disesuaikan dengan kebutuhan spesifik proyek dan tim development.</em></p>',
            
            // Content with comprehensive formatting (showcasing new heading levels)
            '<h1>Panduan Lengkap Best Practices</h1><p>Dalam dunia development, mengikuti best practices adalah kunci kesuksesan proyek jangka panjang.</p><h2>Code Quality Standards</h2><ul><li>Naming conventions yang konsisten<ul><li>Variables: <code>camelCase</code></li><li>Constants: <code>UPPER_SNAKE_CASE</code></li><li>Functions: <code>descriptiveVerbNoun()</code></li></ul></li><li>Code documentation<ul><li>Inline comments untuk logic kompleks</li><li>Function/method documentation</li><li>README file yang comprehensive</li></ul></li><li>Error handling yang robust</li></ul><blockquote><p>"Clean code always looks like it was written by someone who cares." - Robert C. Martin</p></blockquote><h3>Testing Strategy</h3><ol><li>Unit Testing (70%)<ul><li>Test individual functions</li><li>Mock external dependencies</li><li>Achieve high code coverage</li></ol></li><li>Integration Testing (20%)<ul><li>Test component interactions</li><li>Database integration tests</li><li>API endpoint testing</li></ol></li><li>End-to-End Testing (10%)<ul><li>User journey testing</li><li>Cross-browser compatibility</li><li>Performance testing</li></ol></li></ol><h4>Advanced Formatting Examples</h4><p><del>Cara lama:</del> Manual testing saja sudah cukup<br><strong>Cara modern:</strong> Automated testing adalah keharusan</p><h5>Mathematical Expressions</h5><p>Formula kompleksitas: O(n<sup>2</sup>) vs O(n log n)</p><h6>Chemical Formulas</h6><p>H<sub>2</sub>O + CO<sub>2</sub> → H<sub>2</sub>CO<sub>3</sub></p><pre><code>// Example unit test\ndescribe(\'Calculator\', () => {\n  test(\'should add two numbers correctly\', () => {\n    expect(add(2, 3)).toBe(5);\n  });\n});</code></pre>'
        ];
        
        return $this->faker->randomElement($contentVariations);
    }
}