CREATE TABLE library_items (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    item_type VARCHAR(10) NOT NULL
        CHECK (item_type IN ('movie', 'book')),
    genre VARCHAR(100) NOT NULL,
    rating NUMERIC(3,1)
        CHECK (rating >= 0 AND rating <= 10),
    release_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO library_items
    (title, item_type, genre, rating, release_year)
VALUES
    ('Interstellar', 'movie', 'Sci-Fi', 8.7, 2014),
    ('The Dark Knight', 'movie', 'Action', 9.0, 2008),
    ('3 Idiots', 'movie', 'Comedy', 8.4, 2009),
    ('Atomic Habits', 'book', 'Self-Help', 8.5, 2018),
    ('The Alchemist', 'book', 'Fiction', 8.0, 1988);