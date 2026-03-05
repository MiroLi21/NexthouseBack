<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Seeder;

class NextHouseSeeder extends Seeder
{
    public function run(): void
    {
        // ====================================================
        //  الخدمات الرئيسية
        // ====================================================
        $interior = Service::create([
            'name' => 'تصميم داخلي',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/1946/1946488.png',
        ]);

        $exterior = Service::create([
            'name' => 'واجهات خارجية',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/3448/3448628.png',
        ]);

        $garden = Service::create([
            'name' => 'تصميم حدائق',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/628/628283.png',
        ]);

        $offices = Service::create([
            'name' => 'مكاتب وفضاءات تجارية',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/1048/1048966.png',
        ]);

        // ====================================================
        //  أقسام خدمة التصميم الداخلي
        // ====================================================
        $bohemian = Category::create(['name' => 'بوهيمي', 'service_id' => $interior->id]);
        $classic = Category::create(['name' => 'كلاسيك', 'service_id' => $interior->id]);
        $modern = Category::create(['name' => 'مودرن', 'service_id' => $interior->id]);
        $minimalist = Category::create(['name' => 'مينيمالست', 'service_id' => $interior->id]);

        // ====================================================
        //  أقسام خدمة الواجهات الخارجية
        // ====================================================
        $stone = Category::create(['name' => 'حجر طبيعي', 'service_id' => $exterior->id]);
        $glass = Category::create(['name' => 'زجاج وألمنيوم', 'service_id' => $exterior->id]);
        $concrete = Category::create(['name' => 'كونكريت نوعي', 'service_id' => $exterior->id]);

        // ====================================================
        //  أقسام خدمة الحدائق
        // ====================================================
        $tropical = Category::create(['name' => 'استوائي', 'service_id' => $garden->id]);
        $japanese = Category::create(['name' => 'ياباني زن', 'service_id' => $garden->id]);
        $rockGarden = Category::create(['name' => 'حديقة صخرية', 'service_id' => $garden->id]);

        // ====================================================
        //  أقسام خدمة المكاتب
        // ====================================================
        $openSpace = Category::create(['name' => 'مساحات مفتوحة', 'service_id' => $offices->id]);
        $executive = Category::create(['name' => 'مكاتب تنفيذية', 'service_id' => $offices->id]);

        // ====================================================
        //  صور مشاريع — التصميم الداخلي (بوهيمي)
        // ====================================================
        $bohemianProjects = [
            ['url' => 'https://images.unsplash.com/photo-1600210492493-0946911123ea?w=800', 'desc' => 'غرفة نوم بوهيمي بألوان دافئة'],
            ['url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800', 'desc' => 'صالون بوهيمي بلمسات شرقية'],
            ['url' => 'https://images.unsplash.com/photo-1567016432779-094069958ea5?w=800', 'desc' => 'مكتبة منزلية بوهيمي'],
        ];
        foreach ($bohemianProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $bohemian->id]);
        }

        // صور — كلاسيك
        $classicProjects = [
            ['url' => 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800', 'desc' => 'غرفة معيشة كلاسيكية فاخرة'],
            ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800', 'desc' => 'أريكة كلاسيك بالذهبي والأبيض'],
            ['url' => 'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=800', 'desc' => 'غرفة طعام كلاسيكية'],
        ];
        foreach ($classicProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $classic->id]);
        }

        // صور — مودرن
        $modernProjects = [
            ['url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', 'desc' => 'مطبخ مودرن أوبن سبيس'],
            ['url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', 'desc' => 'غرفة نوم مودرن بألوان محايدة'],
            ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 'desc' => 'صالون مودرن مفتوح'],
            ['url' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800', 'desc' => 'غرفة جلوس مودرن مع إضاءة ذكية'],
        ];
        foreach ($modernProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $modern->id]);
        }

        // صور — مينيمالست
        $minimalistProjects = [
            ['url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=800', 'desc' => 'غرفة مينيمالست بالأبيض والخشب'],
            ['url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800', 'desc' => 'مطبخ مينيمالست نظيف'],
            ['url' => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800', 'desc' => 'حمام مينيمالست فاخر'],
        ];
        foreach ($minimalistProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $minimalist->id]);
        }

        // ====================================================
        //  صور مشاريع — الواجهات الخارجية
        // ====================================================
        $stoneProjects = [
            ['url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800', 'desc' => 'فيلا بواجهة حجر طبيعي'],
            ['url' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800', 'desc' => 'بيت ريفي بحجر بيج'],
        ];
        foreach ($stoneProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $stone->id]);
        }

        $glassProjects = [
            ['url' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800', 'desc' => 'ناطحة سحاب بواجهة زجاجية'],
            ['url' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800', 'desc' => 'مبنى تجاري واجهة ألمنيوم'],
        ];
        foreach ($glassProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $glass->id]);
        }

        $concreteProjects = [
            ['url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800', 'desc' => 'فيلا مودرن كونكريت'],
            ['url' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', 'desc' => 'بيت كونكريت مع ديكور مدمج'],
        ];
        foreach ($concreteProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $concrete->id]);
        }

        // ====================================================
        //  صور مشاريع — الحدائق
        // ====================================================
        $tropicalProjects = [
            ['url' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800', 'desc' => 'حديقة استوائية بنخيل وشلال'],
            ['url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800', 'desc' => 'مسبح مع نباتات استوائية'],
        ];
        foreach ($tropicalProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $tropical->id]);
        }

        $japaneseProjects = [
            ['url' => 'https://images.unsplash.com/photo-1503693038939-ab695d25e04a?w=800', 'desc' => 'حديقة يابانية بحجارة وخيزران'],
            ['url' => 'https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=800', 'desc' => 'زن جاردن مع بركة مياه هادئة'],
        ];
        foreach ($japaneseProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $japanese->id]);
        }

        // ====================================================
        //  صور مشاريع — مكاتب
        // ====================================================
        $openSpaceProjects = [
            ['url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800', 'desc' => 'مساحة عمل مفتوحة بتصميم عصري'],
            ['url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=800', 'desc' => 'أوبن سبيس مكتبي مع مناطق استراحة'],
        ];
        foreach ($openSpaceProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $openSpace->id]);
        }

        $executiveProjects = [
            ['url' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=800', 'desc' => 'مكتب تنفيذي فاخر بخشب داكن'],
            ['url' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=800', 'desc' => 'غرفة اجتماعات بانورامية'],
        ];
        foreach ($executiveProjects as $p) {
            Project::create(['image_path' => $p['url'], 'description' => $p['desc'], 'category_id' => $executive->id]);
        }
    }
}
