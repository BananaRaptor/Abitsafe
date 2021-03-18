CREATE EXTENSION IF NOT EXISTS pgcrypto;
drop function if exists encrypt(TEXT);
drop function if exists decrypt(TEXT);
drop function if exists encrypt1step(plaintext TEXT);
drop function if exists decrypt1step(cipher TEXT);

CREATE FUNCTION encrypt(plaintext TEXT) returns TEXT
    LANGUAGE plpgsql AS $$
    DECLARE
        encryption_key TEXT;
        authentication_key TEXT;
    BEGIN
        encryption_key := current_setting('abitsafe.encryption_key')::text;
        BEGIN
            authentication_key := current_setting('abitsafe.authentication_key')::text;
        EXCEPTION
            WHEN OTHERS THEN authentication_key := null;
        END;

        IF authentication_key IS NOT NULL THEN
            plaintext := encode(pgp_sym_encrypt(plaintext,authentication_key),'base64');
        END IF;

        Return encode(pgp_sym_encrypt(plaintext, encryption_key), 'base64');
    END;
    $$;

CREATE FUNCTION decrypt(cipher TEXT) RETURNS text
    language plpgsql AS $$
    DECLARE
        encryption_key TEXT;
        authentication_key text;
        plaintext TEXT;
    BEGIN
        BEGIN
            authentication_key := current_setting('abitsafe.authentication_key')::text;

        EXCEPTION
            WHEN OTHERS THEN authentication_key := null;
        END;

        BEGIN
            encryption_key := current_setting('abitsafe.encryption_key')::text;

            plaintext := pgp_sym_decrypt(decode(cipher, 'base64')::bytea, encryption_key)::text;
            IF authentication_key IS NOT NULL THEN
                plaintext := pgp_sym_decrypt(decode(pgp_sym_decrypt(decode(cipher, 'base64')::bytea, encryption_key),'base64'), authentication_key)::text;
            END IF;
            Return plaintext;

        EXCEPTION
            WHEN OTHERS THEN RETURN '###ENCRYPTED###';
        END;
    END;
    $$;

CREATE FUNCTION encrypt1step(plaintext TEXT) returns TEXT
    LANGUAGE plpgsql AS $$
DECLARE
    encryption_key TEXT;
    authentication_key TEXT;
BEGIN
    encryption_key := current_setting('abitsafe.encryption_key')::text;


    Return encode(pgp_sym_encrypt(plaintext, encryption_key), 'base64');
END;
$$;

CREATE FUNCTION decrypt1step(cipher TEXT) RETURNS text
    language plpgsql AS $$
DECLARE
    encryption_key TEXT;
    authentication_key text;
    plaintext TEXT;
BEGIN

    BEGIN
        encryption_key := current_setting('abitsafe.encryption_key')::text;

        plaintext := pgp_sym_decrypt(decode(cipher, 'base64')::bytea, encryption_key)::text;

        Return plaintext;

    EXCEPTION
        WHEN OTHERS THEN RETURN '###ENCRYPTED###';
    END;
END;
$$;