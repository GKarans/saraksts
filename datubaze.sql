# lietotājs
CREATE TABLE lietotajs(
    lietotajvards varchar(30) NOT NULL PRIMARY KEY,
    parole varchar(255) NOT NULL,
    epasts varchar(30) NOT NULL,
    tel_nr varchar(15),
    loma varchar(15)
);
# saraksts
CREATE TABLE saraksts(
    id int(11) NOT NULL AUTO_INCREMENT,
    nosaukums varchar(100) NOT NULL,
    lietotajvards varchar(30),
    PRIMARY KEY (id),
    FOREIGN KEY (lietotajvards) REFERENCES lietotajs(lietotajvards)
);
# ieraksts
CREATE TABLE ieraksts(
    id int(11) NOT NULL AUTO_INCREMENT,
    teksts varchar(100) NOT NULL,
    izsvitrots BOOLEAN DEFAULT 0,
    saraksts_id int(11),
    PRIMARY KEY (id),
    FOREIGN KEY (saraksts_id) REFERENCES saraksts(id)
);
