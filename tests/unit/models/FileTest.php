<?php
use Hook\Model\File;

class FileTest extends TestCase
{

    public function testBase64Detect()
    {
        $base64_png = File::base64('data:image/png;base64,UftKpGMhKUvUQ5w');
        $this->assertTrue($base64_png[1] == "image/png");
        $this->assertTrue($base64_png[2] == "");
        $this->assertTrue($base64_png[3] == "UftKpGMhKUvUQ5w");

        $base64_jpg = File::base64('data:image/jpg;base64,UftKpGMhKUvUQ5w');
        $this->assertTrue($base64_jpg[1] == "image/jpg");
        $this->assertTrue($base64_jpg[2] == "");
        $this->assertTrue($base64_jpg[3] == "UftKpGMhKUvUQ5w");

        $base64_otf = File::base64('data:application/vnd.ms-opentype; charset=binary;base64,T1RUTwALAIAAAwAwQ');
        $this->assertTrue($base64_otf[1] == "application/vnd.ms-opentype");
        $this->assertTrue($base64_otf[2] == " charset=binary;");
        $this->assertTrue($base64_otf[3] == "T1RUTwALAIAAAwAwQ");

        $base64_otf2 = File::base64('data:application/vnd.ms-opentype;charset=binary;base64,T1RUTwALAIAAAwAwQ');
        $this->assertTrue($base64_otf2[1] == "application/vnd.ms-opentype");
        $this->assertTrue($base64_otf2[2] == "charset=binary;");
        $this->assertTrue($base64_otf2[3] == "T1RUTwALAIAAAwAwQ");

        $string1 = File::base64("this is just a plain string, not a base64,wathever.");
        $this->assertFalse($string1);
    }

}
