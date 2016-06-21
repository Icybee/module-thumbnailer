declare namespace Icybee {

    namespace Thumbnailer {

        namespace Version {

            interface Options {
                'background': string,
                'default': string,
                'filter': string,
                'format': string,
                'height': number,
                'method': string,
                'no-interlace': boolean,
                'no-upscale': boolean,
                'overlay': string,
                'path': string,
                'quality': number,
                'src': string,
                'width': number
            }

            interface ShortOptions {
                b: string,
                d: string,
                fi: string,
                f: string,
                h: number,
                m: string,
                ni: boolean,
                nu: boolean,
                o: string,
                p: string,
                q: number,
                s: string,
                w: number
            }

        }

        class Version {
            static widen(options: Version.ShortOptions)
            static shorten(options: Version.Options)
            static normalize(options: Version.Options)
            static filter(options: Version.Options)
            static serialize(options: Version.Options)
            static unserialize(serialized_options: string)
            constructor(options: Version.Options)
            toString(): string
            background: string
            'default': string
            filter: string
            format: string
            height: number
            method: string
            noInterlace: boolean
            noUpscale: boolean
            overlay: string
            path: string
            quality: number
            src: string
            width: number
        }

    }

}
