_dl_api()
{
    local cur=${COMP_WORDS[COMP_CWORD]}

    local first=$(echo $cur | cut -d ':' -f 1)
    local second=$(echo $cur | cut -d ':' -f 2)

    case "$first" in
      "app")
        COMPREPLY=( $( compgen -W "new" -- $second ) )
        return 0
        ;;

      "config")
        COMPREPLY=( $( compgen -W "set remove" -- $second ) )
        return 0
        ;;

      "generate")
        COMPREPLY=( $( compgen -W "observer route schedule seed template" -- $second ) )
        return 0
        ;;

      "db")
        COMPREPLY=( $( compgen -W "seed" -- $second ) )
        return 0
        ;;

      "key")
        COMPREPLY=( $( compgen -W "new" -- $second ) )
        return 0
        ;;

      "schedule")
        COMPREPLY=( $( compgen -W "upload" -- $second ) )
        return 0
        ;;

      "module")
        COMPREPLY=( $( compgen -W "remove upload" -- $second ) )
        return 0
        ;;

    esac

    # completing an option
    if [[ "$cur" == -* ]]; then
      COMPREPLY=( $( compgen -W "--app --config --debug --endpoint --help --json --version" -- $cur ) )
      return 0
    fi

    COMPREPLY=( $(compgen -W "app: apps console config: db: generate: key: keys module: modules schedule schedule:" -- $cur) )
    return 0
}
complete -F _dl_api -o nospace dl-api
