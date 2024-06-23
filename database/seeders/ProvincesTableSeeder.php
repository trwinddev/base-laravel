<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Carbon\Carbon;

class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = DB::table('dmtinh')->first();
        if (!is_null($provinces)) {
            return;
        }

        $arr_data = [
            [1,'Thành phố Hà Nội', 'Ha Noi City'],
            [2,'Tỉnh Hà Giang', 'Ha Giang Province'],
            [4,'Tỉnh Cao Bằng', 'Cao Bang Province'],
            [6,'Tỉnh Bắc Kạn', 'Bac Kan Province'],
            [8,'Tỉnh Tuyên Quang', 'Tuyen Quang Province'],
            [10,'Tỉnh Lào Cai', 'Lao Cai Province'],
            [11,'Tỉnh Điện Biên', 'Dien Bien Province'],
            [12,'Tỉnh Lai Châu', 'Lai Chau Province'],
            [14,'Tỉnh Sơn La', 'Son La Province'],
            [15,'Tỉnh Yên Bái', 'Yen Bai Province'],
            [17,'Tỉnh Hoà Bình', 'Hoa Binh Province'],
            [19,'Tỉnh Thái Nguyên', 'Thai Nguyen Province'],
            [20,'Tỉnh Lạng Sơn', 'Lang Son Province'],
            [22,'Tỉnh Quảng Ninh', 'Quang Ninh Province'],
            [24,'Tỉnh Bắc Giang', 'Bac Giang Province'],
            [25,'Tỉnh Phú Thọ', 'Phu Tho Province'],
            [26,'Tỉnh Vĩnh Phúc', 'Vinh Phuc Province'],
            [27,'Tỉnh Bắc Ninh', 'Bac Ninh Province'],
            [30,'Tỉnh Hải Dương', "Hai Duong Province"],
            [31,'Thành phố Hải Phòng', 'Hai Phong Province'],
            [33,'Tỉnh Hưng Yên', 'Hung Yen Province'],
            [34,'Tỉnh Thái Bình', 'Thai Binh Province'],
            [35,'Tỉnh Hà Nam', 'Ha Nam Province'],
            [36,'Tỉnh Nam Định', 'Nam Dinh Province'],
            [37,'Tỉnh Ninh Bình', 'Ninh Binh Province'],
            [38,'Tỉnh Thanh Hóa', 'Thanh Hoa Province'],
            [40,'Tỉnh Nghệ An', 'Nghe An Province'],
            [42,'Tỉnh Hà Tĩnh', 'Ha Tinh Province'],
            [44,'Tỉnh Quảng Bình', 'Quang Binh Province'],
            [45,'Tỉnh Quảng Trị', 'Quang Tri Province'],
            [46,'Tỉnh Thừa Thiên Huế', 'Thua Thien Hue Province'],
            [48,'Thành phố Đà Nẵng', 'Da Nang City'],
            [49,'Tỉnh Quảng Nam', 'Quang Nam Province'],
            [51,'Tỉnh Quảng Ngãi', 'Quang Ngai Province'],
            [52,'Tỉnh Bình Định', 'Binh Dinh Province'],
            [54,'Tỉnh Phú Yên', 'Phu Yen Province'],
            [56,'Tỉnh Khánh Hòa', 'Khanh Hoa Province'],
            [58,'Tỉnh Ninh Thuận', 'Ninh Thuan Province'],
            [60,'Tỉnh Bình Thuận', 'Binh Thuan Province'],
            [62,'Tỉnh Kon Tum', 'Kon Tum Province'],
            [64,'Tỉnh Gia Lai', 'Gia Lai Province'],
            [66,'Tỉnh Đắk Lắk', 'Dak Lak Province'],
            [67,'Tỉnh Đắk Nông', 'Dak Nong Province'],
            [68,'Tỉnh Lâm Đồng', 'Lam Dong Province'],
            [70,'Tỉnh Bình Phước', 'Binh Phuoc Province'],
            [72,'Tỉnh Tây Ninh', 'Tay Ninh Province'],
            [74,'Tỉnh Bình Dương', 'Binh Duong Province'],
            [75,'Tỉnh Đồng Nai', 'Dong Nai Province'],
            [77,'Tỉnh Bà Rịa - Vũng Tàu', 'Ba Ria - Vung Tau Province'],
            [79,'Thành phố Hồ Chí Minh', 'Ho Chi Minh City'],
            [80,'Tỉnh Long An', 'Long An Province'],
            [82,'Tỉnh Tiền Giang', 'Tien Giang Province'],
            [83,'Tỉnh Bến Tre', 'Ben Tre Province'],
            [84,'Tỉnh Trà Vinh', 'Tra Vinh Province'],
            [86,'Tỉnh Vĩnh Long', 'Vinh Long Province'],
            [87,'Tỉnh Đồng Tháp', 'Dong Thap Province'],
            [89,'Tỉnh An Giang', 'An Giang Province'],
            [91,'Tỉnh Kiên Giang', 'Kien Giang Province'],
            [92,'Thành phố Cần Thơ', 'Can Tho Province'],
            [93,'Tỉnh Hậu Giang', 'Hau Giang Province'],
            [94,'Tỉnh Sóc Trăng', 'Soc Trang Province'],
            [95,'Tỉnh Bạc Liêu', 'Bac Lieu Province'],
            [96,'Tỉnh Cà Mau', 'Ca Mau Province']
        ];

        foreach($arr_data as $data) {
            DB::table('dmtinh')->insert([
                'matinh' => sprintf("%02s", $data[0]),
                'tentinh' => $data[1],
                'tentinh_en' => $data[2],
                'nam_quan_ly' => Carbon::now()->format("Y"),
                'created_at' => Carbon::now()->format("Y-m-d H:i:s"),
                'updated_at' => Carbon::now()->format("Y-m-d H:i:s")
            ]);
        }
    }
}
