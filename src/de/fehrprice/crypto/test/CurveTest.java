package de.fehrprice.crypto.test;

import static org.junit.Assert.*;

import java.math.BigInteger;

import org.junit.After;
import org.junit.Before;
import org.junit.Test;

import de.fehrprice.crypto.AES;
import de.fehrprice.crypto.Curve25519;
import de.fehrprice.crypto.RSA;

public class CurveTest {
	
	AES aes;
	RSA rsa;
	Curve25519 crv;

	@Before
	public void setUp() throws Exception {
		aes = new AES();
		rsa = new RSA();
		crv = new Curve25519();
	}

	@After
	public void tearDown() throws Exception {
	}

	@Test
	public void testConversions() {
		// Scalars
		String scalar1 = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
		byte[] ar1 = crv.toByteArray(scalar1);
		assertEquals(32, ar1.length);
		assertEquals((byte)0xa5, ar1[0]);
		assertEquals((byte)0xc4, ar1[31]);
		try {
			ar1 = crv.toByteArray("");
			assertEquals(32, ar1.length);
		} catch (IllegalArgumentException e) {}
		
		byte[] ar2 = crv.toByteArrayLittleEndian(scalar1);
		assertEquals((byte)0xc4, ar2[0]);
		assertEquals((byte)0xa5, ar2[31]);

		BigInteger bigScalarAsNumber = new BigInteger("31029842492115040904895560451863089656472772604678260265531221036453811406496");
		BigInteger big1 = crv.decodeLittleEndian(ar2, 255);
		assertNotEquals(bigScalarAsNumber, big1);
		
		String scalarCorrected = "a046e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449a44";
		big1 = crv.decodeLittleEndian(crv.toByteArray(scalarCorrected), 255);
		assertEquals(bigScalarAsNumber, big1);
		
		BigInteger scalar1Decoded = crv.decodeScalar25519(crv.toByteArray(scalar1));
		assertEquals(bigScalarAsNumber, scalar1Decoded);
		
		// U Coordinates
		String ucoord1 = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
		BigInteger biguAsNumber = new BigInteger("34426434033919594451155107781188821651316167215306631574996226621102155684838");
		//System.out.println(biguAsNumber.toString(16));
		BigInteger bigu = crv.decodeLittleEndian(crv.toByteArray(ucoord1), 255);
		assertEquals(biguAsNumber, bigu);
		BigInteger uDecoded = crv.decodeUCoordinate(crv.toByteArray(ucoord1), 255);
		assertEquals(biguAsNumber, uDecoded);
		System.out.println(" u decoded " + uDecoded.toString(16));
		String uEncoded = crv.encodeUCoordinate(uDecoded, 255);
		System.out.println(" u encoded " + uEncoded);
		int x = 0 - 2;
		System.out.println(Integer.toHexString(x));
		//assertEquals("c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552", uDecoded.toString(16));
		//assertEquals("c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552", uEncoded);
//		crv.x25519(scalar1Decoded, uDecoded, 255);
//		BigInteger minus1 = BigInteger.ONE.negate();
//		BigInteger res = minus1.and(new BigInteger("4"));
//		System.out.println("bi invert: " + res.toString(16));
		System.out.println(" skalar decoded " + scalar1Decoded.toString(16));
		crv.out(scalar1Decoded, " skalar decoded ");
		BigInteger outU = crv.x25519(scalar1Decoded, uDecoded, 255);
		//System.out.println("output U: " + outU.toString(16));
		crv.out(outU, "output U:");
		//System.out.println(outU);
	}
	
	/**
	 * Test curve25519 according to RFC 7748, section 5.2. test vectors
	 * Test single curve25519 examples
	 */
	@Test
	public void TestVectors() {
		BigInteger scalar, uIn, uOut;
		String scalarString, uInString, uOutString;
		
		// first set of vectors
		scalarString = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
		uInString    = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
		uOutString   = "c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552";
		
		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(uOutString, crv.asLittleEndianHexString(uOut));
		
		// second set of vectors
		scalarString = "4b66e9d4d1b4673c5ad22691957d6af5c11b6421e0ea01d42ca4169e7918ba0d";
		uInString    = "e5210f12786811d3f4b7959d0538ae2c31dbe7106fc03c3efc4cd549c715a493";
		uOutString   = "95cbde9476e8907d7aade45cb4b873f88b595a68799fa152e6f8f7647aac7957";
		
		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(uOutString, crv.asLittleEndianHexString(uOut));
	}

	/**
	 * Test curve25519 according to RFC 7748, section 5.2. test vectors
	 * Test calling curve25519 multiple times
	 */
	@Test
	public void TestVectorsMulti() {
		BigInteger scalar, uIn, uOut;
		String scalarString, uInString, uOutString1, uOutString1000, uOutString1Mio;

		scalarString   = "0900000000000000000000000000000000000000000000000000000000000000";
		uInString      = "0900000000000000000000000000000000000000000000000000000000000000";
		uOutString1    = "422c8e7a6227d7bca1350b3e2bb7279f7897b87bb6854b783c60e80311ae3079";
		uOutString1000 = "684cf59ba83309552800ef566f2f4d3c1c3887c49360e3875f2eb94d99532c51";
		uOutString1Mio = "7c3911e0ab2586fd864497297e575e6f3bc601c0883c30df5f4dd2d24f665424";

		// one iteration:
		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(uOutString1, crv.asLittleEndianHexString(uOut));
		
		// 1,000 iterations:
		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
		for (int i = 1; i <= 1000; i++) {
			uOut = crv.x25519(scalar, uIn, 255);
			//crv.out(uOut, (i) + ":");
			uIn = crv.decodeUCoordinate(crv.toByteArray(scalarString), 255);
			scalarString = crv.asLittleEndianHexString(uOut);
			scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		}
		assertEquals(uOutString1000, scalarString);
		
		// 1,000,000 iterations:
		scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uInString), 255);
		for (int i = 1; i <= 1000000; i++) {
			uOut = crv.x25519(scalar, uIn, 255);
			if(i % 1000 == 0)
			  crv.out(uOut, (i) + ":");
			uIn = crv.decodeUCoordinate(crv.toByteArray(scalarString), 255);
			scalarString = crv.asLittleEndianHexString(uOut);
			scalar = crv.decodeScalar25519(crv.toByteArray(scalarString));
		}
		assertEquals(uOutString1Mio, scalarString);
	}

	/**
	 * Test curve25519 according to RFC 7748, section 6.1. test vectors
	 * Test Diffie-Hellman protocol
	 */
	@Test
	public void TestDiffieHellman() {
		BigInteger scalar, uIn, uOut, bobPublicKey, alicePublicKey, secretKey;
		String uBasePoint, a, b, a_pub, b_pub, secret_k;

		uBasePoint     = "0900000000000000000000000000000000000000000000000000000000000000";
		// Alice private key (externally generated)
		a              = "77076d0a7318a57d3c16c17251b26645df4c2f87ebc0992ab177fba51db92c2a";
		// Bobs private key (externally generated)
		b              = "5dab087e624a8a4b79e17f8b83800ee66f3bb1292618b6fd1c2f8b27ff88e0eb";
		// Alice and Bobs public keys (generated by curve25519
		a_pub          = "8520f0098930a754748b7ddcb43ef75a0dbf3a0d26381af4eba4a98eaa9b4e6a";
		b_pub          = "de9edb7d7b7dc1b4d35b61c2ece435373f8343c85b78674dadfc7e146f882b4f";
		// shared secret
		secret_k       = "4a5d9d5ba4ce2de1728e3bf480350f25e07e21c947d19e3376f09b3c1e161742";
		
		// compute Alices public key 
		scalar = crv.decodeScalar25519(crv.toByteArray(a));
		uIn = crv.decodeUCoordinate(crv.toByteArray(uBasePoint), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		//crv.out(uOut, "a_out");
		assertEquals(a_pub, crv.asLittleEndianHexString(uOut));
		alicePublicKey = uOut;
		
		// compute Bobs public key
		scalar = crv.decodeScalar25519(crv.toByteArray(b));
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(b_pub, crv.asLittleEndianHexString(uOut));
		bobPublicKey = uOut;
		
		// compute shared secret for both and check
		scalar = crv.decodeScalar25519(crv.toByteArray(a));
		uIn = crv.decodeUCoordinate(crv.toByteArray(b_pub), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(secret_k, crv.asLittleEndianHexString(uOut));
		scalar = crv.decodeScalar25519(crv.toByteArray(b));
		uIn = crv.decodeUCoordinate(crv.toByteArray(a_pub), 255);
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(secret_k, crv.asLittleEndianHexString(uOut));
		secretKey = uOut;
	
		// same, but use precomputed values for public keys:
		scalar = crv.decodeScalar25519(crv.toByteArray(a));
		uIn = bobPublicKey;
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(secretKey, uOut);
		scalar = crv.decodeScalar25519(crv.toByteArray(b));
		uIn = alicePublicKey;
		uOut = crv.x25519(scalar, uIn, 255);
		assertEquals(secretKey, uOut);
	}
	
}

	

	/* tests from RFC docu:
X25519:
 Input scalar:
 a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4
 Input scalar as a number (base 10):
 31029842492115040904895560451863089656
 472772604678260265531221036453811406496
 
 Input u-coordinate:
 e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c
 Input u-coordinate as a number (base 10):
 34426434033919594451155107781188821651
 316167215306631574996226621102155684838
 Output u-coordinate:
 c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552
 
 34426434033919594451155107781188821651316167215306631574996226621102155684838
 57896044618658097711785492504343953926634992332820282019728792003956564819949
*/