#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class RandomExifStringField < StringGenerator
  def initialize
    super
    self.module_name = 'Random Exif Field Generator'
  end

  def generate

    fields = %w(ProcessingSoftware DocumentName ImageDescription Make Model PageName Software ModifyDate Artist
                ImageHistory UserComment UniqueCameraModel LocalizedCameraModel CameraSerialNumber OriginalRawFileName
                ReelName CameraLabel OwnerName SerialNumber Lens)

    self.outputs << fields.sample.chomp
  end
end

RandomExifStringField.new.run